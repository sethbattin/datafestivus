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
        if (event.candidate) {
            _connection.addCandidate(event.candidate, self.id);
        }
    };
    this.peerConnection.ondatachannel = function (e) {
        _connection.setReady(true);
        console.log(self.id + " channel");
        channelReceive = e.channel;
        channelReceive.onmessage = function (event) {
            console.log(self.id + ' received: "' + event.data + '".');
        };
    };

};

RTCData.prototype.offer = function(connection) {
    var self = this;
    var onCreated = function(description){
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
    if (!connection.answer || !connection.candidates[this.id].length){
        return;
    }
    this.peerConnection.setRemoteDescription(connection.answer);
    var iceCandidates = connection.getOtherCandidates(this.id);
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
    this.candidates = [];
        
    // events
    this.onoffer = function(){};
    this.onanswer = function(){};
    this.onaddcandidate = function(){};
    this.onready = function(){};
    this.onunready = function(){};
    
};
ConnectionModel.prototype.init = function(name){
    var offerer = new RTCData(name, this);
    offerer.offer(this);

    var checkComplete = function(conn) {
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
ConnectionModel.prototype.respond = function(name){
    var answerer = new RTCData(name, this);
    
    this.onoffer = function(conn){
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
    for (var i in this.candidates){
        if (i != id){
            result = this.candidates[i];
            break;
        }
    }
    return result;
};
