# Data Festivus
WebRTC for the rest of us.  It's a miracle.

## Goals
This project aims to create a stateless storage system on a server that can facilitate a peer-to-peer connection in WebRTC.  It also implements a WebRTC app that demonstrates its usefulness.  The reason for the server application is:

1. WebRTC requires a 3rd party means of communication in order to establish a peer-to-peer connection.  
2. Every WebRTC tutorial glosses over this problem by connecting a browser window to itself via javascript variables.  
3. Some solutions suggest using 3rd-party services to solve the problem, such as Firebase, which are unnecessary.  
4. The data that needs to be transmitted is simple json; there's no need to complicate sharing it.  

## Dev Journal
### 2016 Jan 3

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

### 2016 Jan 4

After much success late last night, I was able to completely separate both halves of the connection into separate objects.  I was able to synchronize them only through a mock server, which updated the connection object for either side when the other received an update event.  This worked perfectly well.

So this evening, I went a step further, and designed two completely separate pages that could accept the serialized connection object at the various phases of interaction, and establish a connection in distinct browser tabs.  I also tested it on distinct machines on my home network, and succeeded again.  Awesome.  The only remaining portion is to synchronize via an actual webserver, which is once again a trivial task.

After that, I would like to simplify the interface for using the connection.  Then I will use this "library" to construct a [gameloop] library component, which I will use to transmit game state data between two remote machines.  Then money, then power, then women, etc. etc.

[gameloop]: https://bitbucket.org/sbattin/gameloop "gameloop javascript canvas library"

### 2016 Jan 8

Plan for this weekend:
 
1. Change the IM-based example to be less stupid, because currently it is designed around connecting to yourself and handling both tabs.  
2. Implement the server-based system.  
3. Diagnose the failures of the connections when they are attempted over anything more complex than a local network.  
4. Consider updating the example from webrtc.github.io to use out-of-band communication based on the work from 1.

---

I completed number 1.  I think that, with a bit more effort, i could successfully accomplish number 4 in the same manner.  Unfortunately, number 4's work is mostly css and dom cleanup, which I have determined to be the thing I hate more than anything in the world.  It's so painful.

I may change my tune after finishing number 2.  My intuition is that I will have to create a better design representing the information transfer.  So that design change will affect the new "manual" mode, too, even though it is driven by copy-paste.  I am imagining an instance representing a server.  It will be literally true in the case of number 2, and less so in the case of number 1.  Although perhaps the code surroundin the copy-paste will be identical, merely with the onchange events removed.  I'm not sure.

---

Other than an infinite loop completely filling my SSD with apache error logs, server work goes reasonably well.  Creating a one-off php app is as ugly as it ever was, especially for a data service.  C'est la vie.  I also regret my commitment to working sans jquery.  The utility is undeniable.

The design of the js library hasn't really improved yet, though the common features of the copy-paste and the ajax polling seem to be emerging.  With some luck, the will both turn into common javascript objects that utilize the `ConnectionModel` as a prototype.  Or something like that.  The clean API for the library hasn't emerged yet either, that is slightly more troublesome.  It would like to get it to the point where creating a connection is a trivial matter of specifying a name from both directions.  Though i'm only half done, so perhaps that will emerge when it completes. 

### 2016 Jan 9

I fleshed out more work on the server.  There were various problems on both ends, and i spent more of my time revising javascript to update correctly.  It was not easy to do, and i'm quite certain i haven't designed it properly for easy utilization.  That is a shame.  I may give it a little pure design thought more in coming days.

Otherwise, I am running low on initiative for completing it.   

### 2016 Jan 23

Brief hiatus; but it is time to resume debugging the webserver/datastore implementation.  It occurs to me that calling it "stateless" is pretty dumb, since it's saving a specific data value to a server and keeping track of it.  Seems pretty stateful, now that I think about it.  Perhaps I'll leave it alone and simply claim "stateless" communication (http).  It was, afterall, a replacement for a socket server.

I reviewed my earlier entries to regain my bearings, and I was reminded that I have yet to succeed for any connection needing actual NAT to cross a firewall (i.e., the whole point of STUN, TURN, and ICE).  I think this is higher in priority that making the server-based system work, which implies that I should improve the IM-based system.  BUT!  I think that work would really imply forcing the IM-based system to operate more closely to the server-based in terms of synchronizing data.  And if I'm doing that, I will need to make the server-system actually work.  So perhaps I can get a fresh look and refactor both to a more consistent operating method.  That will be my task for today; code review for the purpose of refactoring, then improving.

---

It turns out that trying to debug external network traffic is especially difficult when you aren't actually connected to an external network.  Lesson learned.  I have refamiliarized myself with the code, at least.  Without further debugging possible, it's time to make pure architecture!

* Connection
 * Low level API
 * Creates and answers offers and has ICE events
 * sends and receives data after connection
* ConnectionWrapper
 * reads and writes the serialized connection
 * Determines when to run procedures on either side's connection
 * Provides data traffic interface
* SideBand 
 * Detects changes in the connection wrapper (indirectly via event handling)
 * Adds changes to the connection from the sideband
 * Disposable after connection is complete.

That was a totally worthwhile exercise; here's what I learned.  The ConnectionWrapper is the most important part, and it ought to be able to operate with interchangeable sideband.  Ergo: the sideband is a component of the connectionwrapper at initialization time.
