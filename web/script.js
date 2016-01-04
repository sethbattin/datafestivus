/**
 * Created by seth on 7/6/15.
 */

window.RTCPeerConnection = window.RTCPeerConnection || window.mozRTCPeerConnection || window.webkitRTCPeerConnection;

var instance = 0;

var rtcpc = function(connection){
    
    this.i = instance++;
    
    this.connection = connection;
    
    var pcServers = null;
    var pcConstraint = null;
    
    this.peerConnection = new RTCPeerConnection(pcServers, pcConstraint);
    this.channel = this.peerConnection.createDataChannel('player' + this.i, null);
    
    var self = this;
    var channelReceive = null;
    this.peerConnection.ondatachannel = function(e){
        console.log("channel");
        channelReceive = e.channel;
        channelReceive.onmessage = function(event){
            console.log(self.i + ' received: "' + event.data + '".');
        };
    };
    
    var createOfferError = function(error) {
        console.error("Create offer error", error);
    };
    var createAnswerError = function(error){
        console.error("create answer error", error);
    };

    this.call = function() {
        var self = this;
        var onCreated = function(description){
            self.peerConnection.setLocalDescription(description);
            self.connection.offer = description;
        };
        
        this.peerConnection.createOffer(onCreated, createOfferError);
    };
    this.answer = function(){
        if (!this.connection.offer){
            return;
        }

        var self = this;
        this.peerConnection.onicecandidate = function(event){
            if(event.candidate) {
                console.log('ice', event.candidate.candidate);
                self.connection.candidate = event.candidate;
            }
        };
        var onAnswer = function(description){
            self.peerConnection.setLocalDescription(description);
            self.connection.answer = description;
        };
        
        this.peerConnection.setRemoteDescription(this.connection.offer);
        this.peerConnection.createAnswer(onAnswer, createAnswerError)
    };
    this.complete = function(){
        if (!this.connection.answer || !this.connection.candidate){
            return;
        }
        this.peerConnection.setRemoteDescription(this.connection.answer);
        this.peerConnection.addIceCandidate(this.connection.candidate);
    };
};

var ConnectionModel = function(name){
    this.name = name;
    this.offer = '';
    this.answer = '';
    this.candidate = '';
};


var connection = new ConnectionModel('abcd');
var conn1 = new rtcpc(connection);
var conn2 = new rtcpc(connection);

conn1.call(conn2);

var answerPoll = 0;
var createPoll = 0;

var answer = function(){
    if (connection.answer && connection.candidate){
        console.log("answer", connection.candidate.candidate);
        clearInterval(answerPoll);
        conn1.complete();
    }
};
var create = function(){
    console.log("create");
    if (connection.offer){
        clearInterval(createPoll);
        conn2.answer(conn1);
        answerPoll = setInterval(answer, 20); // > 100ms can prevent successful creation
    }
};

createPoll = setInterval(create, 400);
