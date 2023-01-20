# Installing API Platform

Hello and *welcome* you beautiful people, to a tutorial that's near and dear to my
heart: how to build magnificent castles with *Legos*. Oh, that would be awesome,
wouldn't it? But really, we're here to talk about API Platform Version 3, which I
promise is *as* fun as playing with Legos. Just don't tell my son I said that.

API Platform is, very simply, a tool on top of Symfony that allows us to build
powerful APIs *and* love the process! It's been around for years and, honestly, it's
*crushing it*. They have their own dedicated conference and, they've really outdone
themselves with the latest version 3.

If you're new to API Platform, I wouldn't blame you if you said:

> Come on Ryan... creating an API isn't that hard. It's just returning JSON: a
> bunch of squigglies and brackets!

Ok, that *is* true (at least for the first few endpoints). But wow are there a lot of
little details to keep track of. For example, if you have an API that returns product
data, you'll want that product JSON to be returned in the *same* way with the *same*
fields, across all endpoints. That process is called *serialization*. On top of that,
a lot of APIs now return *extra* fields that describes what the data means. We're
going to see and talk about something called "JSON-LD", which does *exactly* that.

What else? How about documentation? Ideally interactive documentation that's
generated automatically... because we do *not* want to build and maintain that by
hand. Even if you're building an API just for yourself, having documentation is
*awesome*. Paginating collections is also super important, filtering and searching
collections, validation and content-type negotiation, which is where that same product
could be returned as JSON, CSV, or another format. So *yes*, creating an API endpoint
*is* easy. But creating a rich API is another thing *entirely*. And *that's* the
point of API Platform. Oh, and if you're familiar with API Platform Version 2,
version 3 will feel *very* familiar. It's just cleaner, more modern, and more
powerful. So get out your Legos, and let's do this!

## The API Platform Distribution

There are *two* ways to install API Platform. If you find their site and click into
the documentation, you'll see them talk about the API Platform "Distribution". This
is pretty cool! It's a completely pre-made project with Docker that gives you a
place to build your API with Symfony, a React admin area, scaffolding to create a
Next.js frontend and more. Heck, it even gives you a production-ready web server with
extra tools like Mercure for real-time updates. It's *the* most powerful way to use
API Platform.

But... in this tutorial, we're *not* going to use it. I hate nice things! No,
we'll start our Lego project from scratch: with a perfectly normal and boring Symfony
app. Why? Because I want you to see *exactly* how everything works under the
hood. Then, if you want to use this Distribution later on, you *totally* can.

## Project Setup & Our Project

Okay, to be a *true* "API Platform JSON Returning Champion", you should
code along with me! Download the source code from this page. And after unzipping
it, you'll find a `start/` directory with the same code that you see here. This is a
brand new Symfony 6.2 project with... absolutely *nothing* in it. Open up this
`README.md` file for all the setup instructions. The last step will be to open the
project in a terminal and use the Symfony binary to run:

```terminal
symfony serve -d
```

This starts a local web server at `127.0.0.1:8000`. I'll cheat and click that link to
open up... a *completely empty* Symfony 6.2 project. There's literally nothing here
except for this demo homepage.

What *are* we going to build? As we all know, the internet is missing something
terribly important: an application for dragons to boast about their stolen treasures!
Because if there's one thing a dragon likes more than treasure, it's *bragging* about
it. Yup, we'll create a rich API that lets tech savvy dragons post *new* treasures,
*fetch* treasures, *search* treasures from other dragons, etc. And yes, I *did*
just finish reading the Hobbit.

## Installing API Platform

So, let's get API Platform installed! Spin back over to your terminal and run:

```terminal
composer require api
```

This is a Symfony Flex alias. Up here, you can see it's actually installing something
called `api-platform/api-pack`. If you're not familiar, a "pack" in Symfony is, kind
of a *fake* package, that allows you to easily install a *set* of packages. If you
scroll down, it installed `api-platform` *itself*, Doctrine, since I didn't already
have that, and some other packages. At the bottom... let's see... the
`doctrine-bundle` recipe is asking us if we want to include a `docker-compose.yml`
file to help add a database to our project. How nice of it! This is *optional*,
but I'm going to say "p" for "Yes permanently". And... done!

The first thing to see is in the `composer.json` file:

[[[ code('f8a66fd06b') ]]]

As promised, that API Platform pack added a bunch of packages into our project. 
Technically, these aren't *all* required, but this is going to give us a really 
rich experience building our API. And if you run

```terminal
git status
```

... yep! It updated the usual files... and also added a bunch of config files
for the new packages. It *looks* like there's a lot... but looks can be deceiving.
All of these directories are empty... and the config files are small and simple.
We also have some `docker-compose` files that we'll use in a minute to spin
up a database.

So... now that API Platform is installed...  did that *give* us anything yet? It did!
And it's cool! Go back to the browser and head to `/api`. Whoa! We have an API
documentation page! It's *empty* because we don't, ya know, actually *have* an API
just yet, but this is going to come to life very soon.

Next: Let's create our first Doctrine entity and "expose" that as an API Resource.
It's time for some magic.
