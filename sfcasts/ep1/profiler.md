# API Debugging with the Profiler

We're going to be doing some seriously cool and complex stuff with the API platform.
So before we get there, I want to make sure we have a *really* awesome debugging
system set up. Because... sometimes debugging APIs can be a pain! Ever made an
Ajax request in JavaScript and the endpoint explodes in a 500 error full of HTML?
Yea, not helpful.

## Installing the Profiler

So one of the best features of Symfony is its Web Debug Toolbar. But if we're building
an API... there's not going to be a Web Debug Toolbar on the bottom
of these JSON responses. So should we even bother installing that package? The answer
is absolutely!

Spin over to the terminal and run:

```terminal
composer require debug
```

This is another Symfony Flex pack that installs `symfony/debug-pack`. If you pop
pop over to your `composer.json` file, this installed a bunch of good stuff: a
logger for our app.. and then down in `require-dev`, it also added MakerBundle,
DebugBundle, and WebProfilerBundle, which is the most important thing for what
we'll talk about.

## AJAX Requests in the Web Debug Toolbar

Head back to our documentation homepage and refresh. Woh! Awesome! We get the Web
Debug Toolbar down on the bottom! Though... that doesn't really help us because...
all of this info is *literally* for how *documentation* page itself. Not particularly
useful.

What we *really* want is all of this profiler info for any API requests we make.
And we can absolutely get that. Check this out. Use the GET collection endpoint.
Hit "Try it out" and then watch closely down here on the Web Debug Toolbar. When I
hit "Execute"... boom! Because that made an AJAX request the AJAX icon on the Web
Debug Toolbar showed up with that! Want to see *all* the deep profiler info for
that request? Just click the little link on that panel. Yup, as you can see here,
we're now looking at the profiler for the `GET /api/treasures` API call.

## API Platform & Serializer in the Profiler

And there's lots of cool stuff in here. Obviously, there's Performance and all the
normal goodies. But one of *my* favorite parts is the "Exception" tab.

Ok, so if you have an API endpoint and that API endpoint has an error, you can
actually open this part of the profiler to see the *full* beautiful HTML exception
in all its glory - stack trace and all. *So* handy.

There are two other especially interesting spots when working on an API. The first,
no surprise, is the "API Platform" tab. This gives us info about the configuration
for all of our API resources. We're going to talk a lot more about config, but this
shows you the *current* and possible configuration that you could put inside of this
`ApiResource` attribute. That's pretty cool. For example, this shows a `description`
option... and that's one key we've already used!

The other one really useful section in the profiler is relatively new: it's for
the "Serializer". We're going to be talking a *lot* about Symfony's serializer, and
this tool will help us get a look at what's going on internally.

## Finding the Profiler for an API Request

So the big takeaway is that every API request actually has a profiler that you can
find! And there are a few ways to find it. We just say the first: if you're making
an AJAX request - even if it's via your own JavaScript - then you can use the web
debug toolbar to find it.

If you look down here a bit, these are the response headers our API returned. One
is called `X-Debug-Token-Link` which offers us a *second* way to find the profiler
for any API request. This is exactly the URL we were jut at.

The last way to find the profiler is... maybe the simplest. Suppose we go directly
to `/api/dragon-treasure.json`. From here, there's no simple way to get to the
profiler. But now, open up a new tab and manually go to `/_profiler`. Yup! This
shows us a list of the latest request we made... include the GET request we just
made! If you click the little token link... boom! We're inside that profiler.

And, you can click this "Last 10" at any point to get back to that list... and
find whichever request you need.

Sweet debugging tools, check! Next: let's talk about the concept of "operations"
in API platform, which represent these six endpoints. How can we configure these?
Or disable one? Or add more? Let's find out!
