# Data Festivus
WebRTC for the rest of us.  It's a miracle.

Can you believe this: [![Build Status](https://travis-ci.org/sethbattin/datafestivus.svg?branch=master)](https://travis-ci.org/sethbattin/datafestivus)

But seriously, this is a webRTC wrapper for data packet exchange.  Sending audio and video might be cool, but just sending serialized data is more useful.

## Usage

1. Include the library, [datafestivus.js], from this repository.
1. Construct a SideBand object (`var sideband = new DataFestivus.SideBand`) and override the methods `onsave`, `oncomplete`, and `begin`.  Due to implementation details, there are specific requirements for each override.  See [github Issue #1](https://github.com/sethbattin/datafestivus/issues/1)
1. Depending on whether the local code is offering or answering the connection, pass the sideband instance to `DataFestivus.start` or `DataFestivus.reply`, respectively.
1. After the sideband's `oncomplete` is called (indicating a successful connection), use `Datafestivus.send` to transmit a data object via the connection.  Add an event listener function using `Datafestivus.addMessageListener`, which is called with the data object as its single argument.

### Sideband Requirements

`begin` - See [github Issue #2](https://github.com/sethbattin/datafestivus/issues/2) - begin must differentiate offer and answer by whether `sideBand.isStarted` is set to true

## Goals
This project aims to create a stateless storage system on a server that can facilitate a peer-to-peer connection in WebRTC.  It also implements a WebRTC app that demonstrates its usefulness.  The reason for the server application is:

1. WebRTC requires a 3rd party means of communication in order to establish a peer-to-peer connection.  
2. Every WebRTC tutorial glosses over this problem by connecting a browser window to itself via javascript variables.  
3. Some solutions suggest using 3rd-party services to solve the problem, such as [Firebase](https://firebase.google.com/), which are unnecessary.  
4. The data that needs to be transmitted is simple json; there's no need to complicate sharing it.  

## Dev Journal