# Data Festivus
WebRTC for the rest of us.  It's a miracle.

## Goals
This project aims to create a stateless storage system on a server that can facilitate a peer-to-peer connection in WebRTC.  It also implements a WebRTC app that demonstrates its usefulness.  The reason for the server application is:

1. WebRTC requires a 3rd party means of communication in order to establish a peer-to-peer connection.  
2. Every WebRTC tutorial glosses over this problem by connecting a browser window to itself via javascript variables.  
3. Some solutions suggest using 3rd-party services to solve the problem, such as Firebase, which are unneccesary.  
4. The data that needs to be transmitted is simple json; there's no need to complicate sharing it.  
