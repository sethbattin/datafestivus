/**
 * Created by seth on 7/6/15.
 */

window.RTCPeerConnection = 
    window.RTCPeerConnection || 
    window.mozRTCPeerConnection || 
    window.webkitRTCPeerConnection;

var RTCConnection = function(id, connection) {

    this.i = id;
    var _connection = connection;

    var pcServers = null;
    var pcConstraint = null;

    this.peerConnection = new RTCPeerConnection(pcServers, pcConstraint);
    this.channel = this.peerConnection.createDataChannel('player' + this.i, null);

    var self = this;
    var channelReceive = null;
    this.peerConnection.onicecandidate = function (event) {
        if (event.candidate) {
            _connection.addCandidate(event.candidate, self.i);
        }
    };
    this.peerConnection.ondatachannel = function (e) {
        console.log(self.i + " channel");
        channelReceive = e.channel;
        channelReceive.onmessage = function (event) {
            console.log(self.i + ' received: "' + event.data + '".');
        };
    };

};

RTCConnection.prototype.offer = function(connection) {
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
RTCConnection.prototype.answer = function(connection){
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
RTCConnection.prototype.complete = function(connection){
    if (!connection.answer || !connection.candidates[this.i].length){
        return;
    }
    this.peerConnection.setRemoteDescription(connection.answer);
    var iceCandidates = connection.getOtherCandidates(this.i);
    for (var i in iceCandidates){
        this.peerConnection.addIceCandidate(iceCandidates[i]);
    }
};

var ConnectionModel = function(name) {
    this.name = name;
    this.offer = '';
    this.answer = '';
    this.candidates = [];
    this.offerer = null;
    this.answerer = null;
};
ConnectionModel.prototype.init = function(offerer){
    this.offerer = offerer;
    this.offerer.offer(this);
};
ConnectionModel.prototype.respond = function(answerer){
    this.answerer = answerer;
    if (this.offer){
        this.answerer.answer(this);
    }
};
ConnectionModel.prototype.checkComplete = function() {
    if (this.offerer && this.answer){
        var candidates = this.getOtherCandidates(this.offerer.i);
        if (candidates.length){
            this.offerer.complete(this);
        }
    }
};

ConnectionModel.prototype.setAnswer = function(answer) {
    this.answer = answer;
    this.checkComplete();
};
ConnectionModel.prototype.setOffer = function(offer) {
    this.offer = offer;
    this.answerer.answer(this);
};

ConnectionModel.prototype.addCandidate = function(candidate, id){
    if (!this.candidates.hasOwnProperty(id)){
        this.candidates[id] = [];
    }
    this.candidates[id].push(candidate);
    this.checkComplete();
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

var connection = new ConnectionModel('abcd');
connection.init(new RTCConnection(1, connection));
connection.respond(new RTCConnection(2, connection));