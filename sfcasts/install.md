# Setup & Ways to Extend API Platform

Welcome API Platform and dragon fans to Episode 3 of our API Platform series. The
episode where things get... let's say: more advanced and interesting.

Episode 1 was our introduction, and we covered a lot: pagination, filtering and
a lot about serialization: how our API resource objects are turned into JSON and
how the JSON sent by the user is turned *back* into those same objects.

Episode 2 was about security and included things like state processors - the key
to running code before or after saving - custom fields, validation, voters, and more.

## Custom Api Classes?

That's *all* good stuff. But, so far, all of our `#[ApiResource]` classes have been
Doctrine entities. And that's fine! But if your API starts to look different than
your entities, making that works adds complexity: serialization groups, extending
normalizers, etc. At some point, it becomes easier and clearer to stop using your
entity directly for your API and, instead, create a dedicated class. *That* is the
biggest focus of this tutorial... and it'll take us deep into the concept of
state providers and processors... which are basically the core to everything.

## Project Setup

All right, people, let's do this! I recommend POSTing up and coding along with me:
it's more fun and you'll get more out of it. Download the course code from this
page and, when you unzip it, you'll find a `start/` directory with the same code
that I have here. Including the all-important README.MD file, which contains all
the deets to get this tutorial running.

The last step is to spin over, open a terminal into the project, and run

```terminal
symfony serve -d
```

to start the built-in web server at https://127.0.0.1:8000. Say hello to: Treasure
Connect! This is the same project we worked on in episode one and two. I *have*
made a few small changes to it - including fixed a few deprecations - but nothing
major.

The most important page is `/api` where we can see our two API resources:
Treasure and User. And we made these fairly complex! We have sub-resources, 
custom fields, complex security, etc. But again, for both `DragonTreasure` and
`User`, the `#[ApiResource]` attribute is above an *entity* class. In a bit, we'll
re-create this *same* API setup with dedicated classes.

## Custom Controllers? Event Listeners?

Before we hop in, I'm going to search for "API platform extending" to find one of
my favorite pages on the API Platform documentation. It's answers a simple but powerful
question: what are all the different ways that I can extend API platform? For example,
state processors are the best way to run code before or after you save something:
a topic we talked about in the last tutorial.

So, this page is *great* and I want you to know about it. But I'm also here to
mention a couple of things that we are *not* going to talk about. First, we are *not*
going to talk about building custom operations that use custom controllers. Heck,
that's not even in this list! The reason: there's always a better way - a different
extension point - to do that. For example, you might create a custom operation
or even a custom ApiResource class with a state processor that allows you to do
whatever weird work you need to do.

We're also not going to talk about event listeners: these kernel events. It's for
the same reason: there are different extensions points we can use. These events also
only work for REST: they don't work for GraphQL. And... it looks like the next version
of API Platform - version 3.2 - may even *remove* these events in favor of a new
internal system that leverages the state providers and state processors even more.

Ok team: time to get to work. Next, let's leverage a state provider and add a totally
custom field that to one of our resources. But unlike when we did this in the previous
tutorial, this field will be properly documented in our API.
