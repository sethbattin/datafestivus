/**
 * Created by seth on 7/6/15.
 */

window.RTCPeerConnection = window.RTCPeerConnection || window.mozRTCPeerConnection || window.webkitRTCPeerConnection;
window.RTCIceCandidate = window.RTCIceCandidate || window.mozRTCIceCandidate || window.webkitRTCIceCandidate;
window.RTCSessionDescription = window.RTCSessionDescription || window.mozRTCSessionDescription || window.webkitRTCSessionDescription;

var connection = function(name){
    
    this.name = name;  // so we know wtf is going on
    
    var pcServers = null;
    var pcConstraint = null;
    var dataContraint = null;
    
    this.peerConnection = new RTCPeerConnection(pcServers, pcConstraint);
    this.channel = this.peerConnection.createDataChannel(
        'sendDataChannel', dataContraint);
    
    var self = this;
    var channelReceive = null;
    this.peerConnection.ondatachannel = function(e){
        console.log("setting data channel on " + self.name);
        channelReceive = e.channel;
        channelReceive.onmessage = function(event){
            console.log(self.name + ' received: "' + event.data + '".');
        }
    };
    
    var onCreatedDescription = function(description){
        var success = function(){
            console.log(self.name + ' created description', JSON.stringify({'sdp': description}));
        };
        var error = function(){
            console.error("set description error");
        };
        this.peerConnection.setLocalDescription(description, success, error);
    };
    
    var createOfferError = function(error) {
        console.error("Create offer error", error);
    };
    var createAnswerError = function(error){
        console.error("create answer error", error);
    };
    //
    //var options = {
    //    //iceRestart: true, 
    //    mandatory: {
    //        OfferToReceiveAudio: 0
    //    }
    //};

    this.call = function(answerer) {
        var onicecandidate = function(event){
            if(event.candidate) {
                console.log('got offerer ice candidate',
                    JSON.stringify({'ice': event.candidate}));
                answerer.peerConnection.addIceCandidate(event.candidate);
            } else {
                console.log("got null ice candidate in caller.", event);
            }
        };
        this.peerConnection.onicecandidate = onicecandidate;

        var self = this;
        var onicecandidate2 = function(event){
            if(event.candidate) {
                console.log('got answerer ice candidate',
                    JSON.stringify({'ice': event.candidate}));
                self.peerConnection.addIceCandidate(event.candidate);
            } else {
                console.log("got null ice candidate in answerer.", event);
            }
        };
        answerer.peerConnection.onicecandidate = onicecandidate2;
        
        var onAnswer = function(description){
            onCreatedDescription.call(answerer, description);
            self.peerConnection.setRemoteDescription(description);
        };
        var onCreated = function(description){
            onCreatedDescription.call(self, description);
            answerer.peerConnection.setRemoteDescription(description);
            answerer.peerConnection.createAnswer(onAnswer, createAnswerError)
        };
        
        this.peerConnection.createOffer(onCreated, createOfferError);
    };
};

var conn1 = new connection('conn1');
var conn2 = new connection('conn2');
conn1.call(conn2);
