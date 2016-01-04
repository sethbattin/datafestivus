# Data Festivus
WebRTC for the rest of us.  It's a miracle.

## Goals
This project aims to create a stateless storage system on a server that can facilitate a peer-to-peer connection in WebRTC.  It also implements a WebRTC app that demonstrates its usefulness.  The reason for the server application is:

1. WebRTC requires a 3rd party means of communication in order to establish a peer-to-peer connection.  
2. Every WebRTC tutorial glosses over this problem by connecting a browser window to itself via javascript variables.  
3. Some solutions suggest using 3rd-party services to solve the problem, such as Firebase, which are unnecessary.  
4. The data that needs to be transmitted is simple json; there's no need to complicate sharing it.  

## Dev Journal
2016 Jan 3

This project has been frustrating to say the least.  Originally begun in July of 2015 in service of [Radminton] multiplayer, the initial goal was to simply create a stateless-server based means of initiating a WebRTC connection between two parties.  This seemed simple, because the data for establishing the connection were easily serializable for storage anywhere, implying that the server itself would be straightforward.  However, that goal quickly fell by the wayside, as implementing even the simplest of WebRTC connections, sans any server, proved to be excessively difficult.

The first problem was that WebRTC tutorials largely focused on the audio/video portion of the API.  Although that is clearly a more impressive demonstration than simple data packets, it emphasized unimportant (to me) features of the API to the detriment of clear explanation of basics.  Everything from STUN/TURN server lists to constructor arguments served these other goals, which obfuscated the remaining parts of the API usage.  This problem was part of the impetus of the project, but the problem persisted during the project's work as well.

The next problem was, even with [a clear tutorial for transmitting strings](1), the example implementation was not easily adaptable for my purpose.  My purpose being to share the parts of the connection through some external mechanism like a "real" peer-to-peer connection would need to.  Rather, the tutorials' implementations allowed each side of the connection to share the same <del>global variable soup</del> window scope.  Each connection got side-specific copies of callbacks which all made closures over both sides of the connection.  Obviously, such an implementation is arguably cheating, and it is useless for anyone actually attempting to implement a connection between two different browsers.

To be honest, it did become clear that another data-share scheme like app engine, firebase, or even some manner of websocket use would have been superior.  But still not required, I shall prove...hopefully.

These example implementations were lacking for other reasons, as well.  In some cases, they missed obvious use case coverage, such as two-way communication.  In others, they did additional wiring which turned out to be utterly unnecessary, as the functionality remained even after they were removed.  This might be evident from my recent commits.

So in attempting to create my own implementation, I modified my examples in service of my own goals.  Unfortunately my understanding was insufficient, and I had a nonfunctional connection when I got done.  It was doubly frustrating to know that I could see a connection working in these bad implementations, but I couldn't make it work any other way.

Next problem, in debugging my mistakes, I found the tools and API output to be wholly insufficient.  Because the API involves many asynchronous steps that operate with nothing observable happening, diagnosing my errors was all but impossible.  It was log messages or bust (although chrome://webrtc-internals/ was not *entirely* useless).  

So, with no better options available, I reverted my code to closer to the crappy implementation, doing my best to keep the global usage to the minimum.  Success; a workin connection.  I then gradually plucked out the data to avoid callbacks entirely, mimicking what would need to be synched in my data server.  I poll over this object, and I can establish connections equally well as before.

 [Radminton]: http://itsobviously.com/radminton/ "RADminton game"
 [1]: https://webrtc.github.io/samples/src/content/datachannel/basic/ "WebRTC Samples: transmitting text"
