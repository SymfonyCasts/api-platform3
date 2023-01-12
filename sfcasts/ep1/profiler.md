# API Debugging with the Profiler

We're going to be doing some seriously cool and complex stuff with the API platform.
So before we get there, I want to make sure we have a really awesome debugging system
set up for our api because sometimes debugging APIs can be a pain. Like if you're
writing JavaScript that's making an ax request your API, and then it explodes with an
air, I don't know if you've ever been like me, but you end up basically looking at an
HTL of the error down your console. We need a better way to handle things. So one of
the best features of Symfony is of course it's web devo toolbar, but if we're
building an API that returns json, there's not exactly going to be a web Divo toolbar
on the bottom of these. So should we even bother installing that package? The answer
is absolutely spin over to terminal and run composer require debug. This is another
Symfony flex pack that installs Symfony /debug pack. And what this really does, if
you pop over to your composer adjacent file is installs several things and installs a
logger for our library. And then if we find the required dev, it also installed maker
bundle debug bundle and web profiler bundle. So a number of different debugging
tools. The most important thing for us is the web profiler bundle.

So thing number one that's cool is that our documentation is an HTML page. So if we
refresh, we get the web debug to a bar down here on the bottom. However, that doesn't
really help us because if we click on any of these links, it's going to open up the
profiler for the documentation page. So you can see, I guess, how fast your
documentation page is working. What we really want is the profiler for our
AJAXrequest, and we can absolutely get that. So check this out. Let's use the GI
collection endpoint. I'm going to hit try it out and watch down here on the web debug
toolbar when I hit Execute Boom, because that made an ax request the ax icon on the
web debug toolbar popped up with that. And if we want to see the profiler for that a
P I request, we can click this little link here and open it up. So we are now, as you
can see here, looking at the profiler for that hx request that return, J S O N.

And there's lots of cool stuff we can see in here. Obviously performance, all the
normal things, but one of the most useful parts of this actually is exception. So if
you have an API endpoint and that API endpoint has an error, you can actually open
this up and see the full beautiful HTML error right here as if it was an HTML page.
So that's super handy. Couple other things we're going to be looking at here is the
API platform tab. This gives us a lot of information about the configuration for all
of our API resources. We're going to be talking more about this later, but this is
actually telling you all the current configuration and possible configuration that
you could put inside of this API resource attribute right here. So that's pretty
cool. So for example, we have description, you can see our description right here.
The other one that's going to be useful and is fairly relatively new is the
serializer. We're going to be talking a lot about Symfonys, serializer, and this is
going to be a tool that's going to help us look at what's going on inside the
serializer.

So the big takeaway is that every age ex AJAXrequest, every a API request actually
has a profile that you can find. And there are a couple ways to find it. The first we
saw is if you're making an AJAXrequest, even if it's via your own JavaScript, you can
use the web devo toolbar to find it. You can also, let's see, if you look down here,
these are the response headers that came back. You can see that there's an X debug
token link, which actually points you to the view URL you can go to to see the
profiler for this request. So that will match what we had up there earlier. The other
way you can do it is, for example, if I just go directly to /api /dragon treasure
json, he says, there's no simple way for me to get to the profiler from here. But if
you actually just open up a new tab and go to slash_profiler, that will instantly
show you the latest request that were made. So you can see our request right here.
There's a get request, so I can click this little token on here to jump into the
profiler for that, and you can actually get to that search at any time by name. This
last 10. So last 10, oh, yep, there's the request. I made a second go and now we can
get all the info that we want about it.

So now that we've got some really sweet debugging tool set up, next, let's talk about
api, the concept of operations in API platform, which represent these six things.
Here we're going to talk about disabling some and also configuration you can do under
each of those.
