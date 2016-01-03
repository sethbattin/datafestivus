/**
 * Created by seth on 7/6/15.
 */

window.RTCPeerConnection = window.RTCPeerConnection || window.mozRTCPeerConnection || window.webkitRTCPeerConnection;

var rtcpc = function(connectionName){
    
    this.connection = 
    
    var pcServers = null;
    var pcConstraint = null;
    
    this.peerConnection = new RTCPeerConnection(pcServers, pcConstraint);
    this.channel = this.peerConnection.createDataChannel('send', null);
    
    var self = this;
    var channelReceive = null;
    this.peerConnection.ondatachannel = function(e){
        channelReceive = e.channel;
        channelReceive.onmessage = function(event){
            console.log(connectionName + ' received: "' + event.data + '".');
        };
    };
    
    var createOfferError = function(error) {
        console.error("Create offer error", error);
    };
    var createAnswerError = function(error){
        console.error("create answer error", error);
    };

    this.call = function(answerer) {
        
        var onCreated = function(description){
            self.peerConnection.setLocalDescription(description);
            answerer.peerConnection.setRemoteDescription(description);
            answerer.answer(self);
        };
        
        this.peerConnection.createOffer(onCreated, createOfferError);
    };
    this.answer = function(caller){
        this.peerConnection.onicecandidate = function(event){
            if(event.candidate) {
                caller.peerConnection.addIceCandidate(event.candidate);
            }
        };
        var self = this;
        var onAnswer = function(description){
            self.peerConnection.setLocalDescription(description);
            caller.peerConnection.setRemoteDescription(description);
        };
        this.peerConnection.createAnswer(onAnswer, createAnswerError)
    }
};

var conn1 = new rtcpc('conn1');
var conn2 = new rtcpc('conn2');
conn1.call(conn2);