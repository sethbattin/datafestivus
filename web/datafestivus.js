(function(global, IO){
    global.RTCPeerConnection =
        global.RTCPeerConnection ||
        global.mozRTCPeerConnection ||
        global.webkitRTCPeerConnection;
    
    var DataFestivus = global.DataFestivus = function() {
        this.connectionModel = null;
        this.peerConnection = null;
        this.channel = null;
        this.channelRecieve = null;
        this.host = false;
        this.sideBand = null;
        this.messageListeners = [];
    };
    
    // a serializable object representing connection properties
    var ConnectionModel = function(){
        // these fields are serialized
        this.offer = '';
        this.answer = '';
        this.candidates = {};
        
        // overridable callbacks
        this.onoffer = function(rtcSessionDescription){
            IO.log('Offer set: ', rtcSessionDescription);
        };
        this.onanswer = function(rtcSessionDescription){
            IO.log('Answer set: ', rtcSessionDescription);
        };        
    };
    var candidateParseId = function(candidateString){
        var result = null;
        var match = null;
        if (candidateString){
            match = /candidate:([0-9]+)/.exec(candidateString);
        }
        if (match && match.length && match[1]){
            result = match[1];
        }
        return result;
    };
    // overridden in init()
    ConnectionModel.prototype.onaddcandidate = function(rtcIceCandidate){
        IO.log("adding a candidate:", rtcIceCandidate);
    };
    ConnectionModel.prototype.addCandidate = function(rtcIceCandidate){
        var id = candidateParseId(rtcIceCandidate.candidate);
        IO.log("adding candidate: ", id);
        var preexisting = this.candidates.hasOwnProperty(id);
        this.candidates[id] = rtcIceCandidate;
        return !preexisting;
    };
    ConnectionModel.prototype.setOffer = function(description){
        if (!description instanceof RTCSessionDescription){
            IO.error("invalid rtc offer.");
            return;
        }
        if (JSON.stringify(description) != JSON.stringify(this.offer)) {
            this.offer = description;
            this.onoffer(description);
        }
    };
    ConnectionModel.prototype.setAnswer = function(description){
        if (!description instanceof RTCSessionDescription){
            IO.error("invalid rtc answer.");
            return;
        }
        if (JSON.stringify(description) != JSON.stringify(this.answer)) {
            this.answer = description;
            this.onanswer(description);
        }
    };
    ConnectionModel.prototype.serialize = function(){
        var data = {
            'offer'         : this.offer,
            'answer'        : this.answer,
            'candidates'    : this.candidates
        };
        return JSON.stringify(data);
    };
    ConnectionModel.prototype.unserialize = function(data){
        if (data.hasOwnProperty('offer') && data.offer){
            var offer = new RTCSessionDescription(data.offer);
            this.setOffer(offer);
        }
        if (data.hasOwnProperty('answer') && data.answer){
            var answer = new RTCSessionDescription(data.answer);
            this.setAnswer(answer);
        }
        if (data.hasOwnProperty('candidates')){
            for (var i = 0; i < data.candidates.length; ++i){
                var rtcIceCandidate = new RTCIceCandidate(data.candidates[i]);
                this.addCandidate(rtcIceCandidate);
                this.onaddcandidate(rtcIceCandidate);
            }
        }
    };
    DataFestivus.ConnectionModel = ConnectionModel;

    // a prototypical implementation of the "side-band" communication for WebRTC
    var SideBand = function(){
        // functions to-be-overridden in useful implementations
        this.onsave = function(connectionModelData){
            IO.log("(not) saving connection model:", connectionModelData);
        };
        this.oncomplete = function(connectionModel){
            IO.log("connection established:", connectionModel);
        };
        this.begin = function(){
            IO.log("NOT starting a sideband load.");
        }
    };
    SideBand.prototype.save = function(connectionModel) {
        if (connectionModel && connectionModel instanceof ConnectionModel) {
            this.onsave(connectionModel.serialize());
        } else {
            IO.error("invalid ConnectionModel")
        }
    };
    
    // this event is not meant to be overridden; it is used below in init()
    SideBand.prototype.onupdate = function(connectionModel){
        IO.log("received updated model json:", connectionModelJson);
    };
    DataFestivus.SideBand = SideBand;

    var init = null;
    
    DataFestivus.prototype.addMessageListener = function(listener){
        if (!(listener instanceof 'function')){
            IO.error('invalid listener');
            return;
        }
        for (var i = 0; i < this.messageListeners.length; ++i){
            if (listener == this.messageListeners[i]){
                return;
            }
        }
        this.messageListeners.push(listener);
    };
    DataFestivus.prototype.removeMessageListener = function(listener){
        for (var i = 0; i < this.messageListeners.length; ++i){
            if (listener == this.messageListeners[i]){
                this.messageListeners.splice(i, 1);
                return;
            }
        }
    };
    
    DataFestivus.prototype.send = function(data) {
        if (this.peerConnection.ready) {
            this.channel.send(data);
        } else {
            IO.error('peer connection is not ready.');
        }
    };
    
    DataFestivus.prototype.start = function(sideBand) {
        this.host = true;
        init.call(this, sideBand);
        var self = this;
        this.peerConnection.createOffer(
            function(description){
                self.peerConnection.setLocalDescription(description);
                self.connectionModel.setOffer(description);
                self.sideBand.save(self.connectionModel);
            },
            function (error){
                IO.error("createOffer() error: ", error)
            }
        );
        this.connectionModel.onoffer = function(){
            self.sideBand.begin();
        };
        this.connectionModel.onanswer = function(rtcSessionDescription){
            self.peerConnection.setRemoteDescription(rtcSessionDescription);
        };
    };
    DataFestivus.prototype.reply = function(sideBand) {
        init.call(this, sideBand);
        var self = this;
        this.connectionModel.onoffer = function(rtcSessionDescription){
            self.peerConnection.setRemoteDescription(rtcSessionDescription);
            self.peerConnection.createAnswer(
                function(description){
                    self.peerConnection.setLocalDescription(description);
                    self.connectionModel.setAnswer(description);
                    self.sideBand.save(self.connectionModel);
                }
            );
        };
        this.sideBand.isStarted = true;
        this.sideBand.begin();
        
    };


    init = function(sideBand) {

        this.sideBand = sideBand;

        var pcServers = null;
        var pcConstraint = null;
        this.peerConnection = new RTCPeerConnection(pcServers, pcConstraint);

        var channelName = this.host ? 'offerer' : 'answerer';
        this.channel = this.peerConnection.createDataChannel(channelName, null);

        this.connectionModel = new ConnectionModel();

        var self = this;

        this.peerConnection.onicecandidate = function(e){
            if (e.candidate){
                if (self.connectionModel.addCandidate(e.candidate, channelName)){
                    self.sideBand.save(self.connectionModel);
                }
            }
        };
        this.connectionModel.onaddcandidate = function(candidate){
            self.peerConnection.addIceCandidate(candidate);
        };
        this.peerConnection.ondatachannel = function(e){
            self.ready = true;
            IO.log('channel ready.');
            self.channelRecieve = e.channel;
            self.onmessage = function(_e) {
                IO.log('message received: ', _e.data);
                for (var i in self.messageListeners) {
                    self.messageListeners[i](_e.data);
                }
            };
            self.sideBand.oncomplete(self.connectionModel);
        };
        this.sideBand.onupdate = function(connectionModelJson){
            self.connectionModel.unserialize(connectionModelJson);
        };

    };
    
})(window, console);