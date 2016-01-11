/**
 * Created by seth on 7/6/15.
 */

window.RTCPeerConnection = 
    window.RTCPeerConnection || 
    window.mozRTCPeerConnection || 
    window.webkitRTCPeerConnection;

var RTCData = function(id, _connection) {
    
    this.id = id;
    var pcServers = null;
    var pcConstraint = null;

    this.peerConnection = new RTCPeerConnection(pcServers, pcConstraint);
    this.channel = this.peerConnection.createDataChannel('player' + this.id, null);

    var self = this;
    var channelReceive = null;
    this.peerConnection.onicecandidate = function (event) {
        console.log(self.id + ' ice');
        if (event.candidate) {
            _connection.addCandidate(event.candidate, self.id);
        }
    };
    
    this.send = function(data) {
        if (_connection.ready) {
            this.channel.send(data);
        }
    };
    
    // TODO: revise this to allow multiple observers, or some such?
    // Closure problems with using self; can't update the property directly
    // on the object.  This indirection allows the message to change.
    var message = function(data) {
        console.log(self.id + ' received: "', data );
    };
    this.setMessage = function(_message){
        message = _message;
    };
    this.peerConnection.ondatachannel = function (e) {
        _connection.setReady(true);
        console.log(self.id + " channel");
        channelReceive = e.channel;
        channelReceive.onmessage = function (event) {
            message(event.data);
        };
        _connection.setReady(true);
    };

};

RTCData.prototype.offer = function(connection) {
    var self = this;
    var onCreated = function(description){
        //console.log(self.id + " offer");
        self.peerConnection.setLocalDescription(description);
        connection.setOffer(description);
    };
    var createOfferError = function(error) {
        console.error("Create offer error", error);
    };
    
    this.peerConnection.createOffer(onCreated, createOfferError);
};
RTCData.prototype.answer = function(connection){
    if (!connection.offer){
        return;
    }

    var self = this;
    var onAnswer = function(description){
        //console.log(self.id + " answer");
        self.peerConnection.setLocalDescription(description);
        connection.setAnswer(description);
    };
    var createAnswerError = function(error){
        console.error("create answer error", error);
    };
    
    this.peerConnection.setRemoteDescription(connection.offer);
    this.peerConnection.createAnswer(onAnswer, createAnswerError)
};
RTCData.prototype.complete = function(connection){
    //console.log(this.id + ' completing');
    var iceCandidates = connection.getOtherCandidates(this.id);
    if (!connection.answer || !iceCandidates.length){
        return;
    }
    this.peerConnection.setRemoteDescription(connection.answer);
    for (var i in iceCandidates){
        this.peerConnection.addIceCandidate(iceCandidates[i]);
    }
};

var ConnectionModel = function(name) {
    
    // this is used for identification only
    this.name = name;

    // these fields are serialized
    this.offer = '';
    this.answer = '';
    this.candidates = {};
        
    // events
    this.onoffer = function(){};
    this.onanswer = function(){};
    this.onaddcandidate = function(){};
    this.onready = function(){};
    this.onunready = function(){};
    
};
ConnectionModel.prototype.init = function(){
    var name = 'offerer';
    var offerer = new RTCData(name, this);
    offerer.offer(this);

    var checkComplete = function(conn) {
        //console.log('on candidate/answer');
        if (conn.answer){
            var candidates = conn.getOtherCandidates(name);
            if (candidates.length){
                offerer.complete(conn);
            }
        }
    };

    this.onaddcandidate = checkComplete;
    this.onanswer = checkComplete;
    
    return offerer;
};
ConnectionModel.prototype.respond = function(){
    var name = 'answerer';
    var answerer = new RTCData(name, this);
    
    this.onoffer = function(conn){
        //console.log('on offer');
        if (conn.offer) {
            answerer.answer(conn);
        }
    };
    
    return answerer;
};  
ConnectionModel.prototype.setReady = function(val){
    this.ready = val;
    if (this.ready){
        this.onready(this);
    } else {
        this.onunready(this);
    }
};

ConnectionModel.prototype.setAnswer = function(answer) {
    this.answer = answer;
    this.onanswer(this);
};
ConnectionModel.prototype.setOffer = function(offer) {
    this.offer = offer;
    this.onoffer(this);
};

ConnectionModel.prototype.addCandidate = function(candidate, id){
    if (!this.candidates.hasOwnProperty(id)){
        this.candidates[id] = [];
    }
    this.candidates[id].push(candidate);
    this.onaddcandidate(this);
};
ConnectionModel.prototype.getOtherCandidates = function(id){
    var result = [];
    for (var _id in this.candidates){
        if (_id != id){
            result = this.candidates[_id];
            break;
        }
    }
    return result;
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
    var json = JSON.parse(data);
    var compareRTCSession = function(jsonObj, RTCSess){
        var incoming = JSON.stringify(jsonObj);
        var current = JSON.stringify(RTCSess);
        return (incoming == current);
    };
    if (json.hasOwnProperty('offer') && json.offer){
        if (!compareRTCSession(json.offer, this.offer)) {
            var offer = new RTCSessionDescription(json.offer);
            this.setOffer(offer);
        }
    }
    if (json.hasOwnProperty('answer') && json.answer){
        if (!compareRTCSession(json.answer, this.answer)){
            var answer = new RTCSessionDescription(json.answer);
            this.setAnswer(answer);
        }
    }
    if (json.hasOwnProperty('candidates')){
        for (var _id in json.candidates){
            for (var i = 0; i < json.candidates[_id].length; ++i){
                var iceCandidate = new RTCIceCandidate(json.candidates[_id][i]);
                this.addCandidate(iceCandidate, _id);
            }
        }
    }
};