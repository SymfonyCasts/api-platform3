# Installing API Platform

Hello and *welcome*, beautiful people, to a tutorial that's near and dear to my heart - how to build really cool castles with *Legos*. Okay, that *would* be awesome. But we're *really* here to talk about API Platform Version 3, which is seriously just as fun as playing with Legos. Just don't tell my son I said that. API Platform is, very simply, a tool built on top of Symfony that allows us to build powerful APIs and absolutely *love* the process. It's been around for a while at this point and, honestly, it's *crushing it*. The latest Version 3 is another huge step forward.

You might be thinking:

`Hey, creating an API endpoint isn't that hard. It's just returning some JSON.`

And yeah, that *is* true (at least for the first few endpoints), but there are a lot of little details to keep track of. For example, if you have an API that returns product data, you need to make sure that the product JSON is always returned in the *same* way with the *same* fields, no matter which endpoint we're using. That process is called *serialization*. On top of that, a lot of APIs now return *extra* information along with that data that describes what the data means. We're going to see and talk about something called a "JSON-LD", which does *exactly* that.

Of course, if you want a really nice API, you'll also want documentation that's, *ideally*, interactive and generated automatically, because we do *not* want to build and maintain that by hand. Even if you have a private API, having documentation is *awesome*. Pagination is super important too, for when you have *a lot* of products, or filtering where you need to *search* those products, or validation, or content type negotiation, which is where that same product could be returned as JSON, CSV, or another format. So *yes*, creating an API endpoint is *easy*, but creating a rich API is another thing *entirely*, and that's the purpose of API Platform. If you're familiar with API Platform Version 2, Version 3 will feel *very* familiar. It's just cleaner, more moderate, and more powerful. So buckle up and let's do this!

There are *two* ways to install API Platform. If you find their site and click into the documentation, you'll see them talk about the API Platform "Distribution". This is pretty cool! It's a completely pre-made project with Docker that's going to give you a place to build your API with Symfony, a React admin, scaffolding to build a Next.js frontend, and even a web server. It's the most powerful way to use API Platform. However, in this tutorial, we're *not* going to use this. Instead, we're going to install API Platform into a new, perfectly normal and boring Symfony project, because I want you to see *exactly* how everything works under the hood. If you want to utilize this Distribution later on, you *totally* can.

Okay, to be a *true* "API Platform JSON Throwing Champion", you should *definitely* code along with me. You can download the source code from this page. After unzipping it, you'll find a start directory with the same code that you see here. This is a brand new Symfony 6.2 project with absolutely *nothing* in it, and you can open up this `README.md` file for all the setup instructions. The last step will be to open the project in the terminal and use the Symfony binary to run

```terminal
symfony serve -d
```

to start a local web server at `127.0.0.1:8000`. I'll cheat and click that link to open up... a *completely empty* Symfony 6.2 project. There's nothing here except for this demo homepage. We're going to create an application where dragons can post *all about* the treasures that they've plundered, because if there's one thing a dragon likes more than treasure, it's *bragging* about it. Our job is to create a rich API that lets tech savvy dragons post *new* treasures, *fetch* treasures, *search* treasures from other dragons, and *more*. Let's get API Platform installed!

Spin back over to your terminal and run:

```terminal
composer require api
```

This is a Symfony Flex alias. Up here, you can see it's actually installing something called `api-platform/api-pack`. If you're not familiar with this, a "pack" in Symfony is just a *fake* package that allows you to easily install a *set* of packages. If you scroll down, we can see it installed `api-platform` *itself*, Doctrine, since I didn't already have that installed, and several other packages. At the bottom... let's see... the `doctrine-bundle` recipe is asking us if we want to include a `docker-compose.yml` file to help us add a database to our project. That's *optional*, but I'm going to say "p" for "Yes permanently" and... perfect!

The first thing to see is in the `composer.json` file. As promised, that API Platform pack installed a bunch of items into our project. Technically, *all* of these aren't required, but this is going to give us a really rich experience building our API. This is everything we need, and if you run

```terminal
git status
```

... yep! It updated the usual files and also added a bunch of configuration files for those new packages. It *looks* like there's a lot here, but looks can be deceiving. All of these directories are empty, and these are just simple configuration files. We also have some `docker-compose` files that we'll use in a second to spin up our database.

We're really on a high level here. We just installed API Platform into our project, the end result of which is, if you go back to the browser and go to `/api`... whoa! We now have an API documentation page! It's *empty* because we don't have anything in our API just yet, but this is going to come to life very soon.

Next: Let's create our first Doctrine entity and expose that to our API so we can set up our API endpoints.
