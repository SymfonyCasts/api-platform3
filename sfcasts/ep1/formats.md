# More Formats: HAL & CSV

API Platform supports multiple input and output formats. We know that we can go to
`/api/treasures.json` to see JSON, `.jsonld` to see JSON-LD or even `.html` to
see the HTML output format.

## Accept Header & Content Negotiation

But adding this extension to the end of the URL is just a hack that API Platform
allows. To choose the format we want the API to return, we're supposed to send an
`Accept` header. And we can see this when we use the interactive docs.
This makes a request with an `Accept` header set to `application/ld+json`. Setting
this header is easy to do in JavaScript, and if you *don't* set it,
JSON-LD is the default format.

API Platform uses *three* formats out of the box. You can see them down here on the
bottom of the docs page. But, what in our app says that we want to use these three
formats specifically? To answer that, head over to your terminal and run:

```terminal
./bin/console debug:config api_platform
```

Inside the config, check out this `formats` key... which, by default, is set to
those three formats. This basically says that if the `Accept` header is
`application/ld+json`, use the JSON-LD format. *Internally*, it means that when
Symfony is serializing our data, it will *serialize* to JSON-LD or JSON.

## Adding a New Format

As a challenge, let's add a *fourth* format. To do that, we just need to add a
new item to this config... but without completely *replacing* the existing formats.
Copy these, then open the `/config/packages/` directory. We don't have an
`api_platform.yaml` file yet, so let's create one. Inside that, say `api_platform`
and paste those below. And while we don't *have* to, I'm going to change this to
use a shorter, more attractive version of this config:

[[[ code('d89758fd53') ]]]

Done!

If we go and refresh right now, everything works the same. We have the *same*
formats below... because we simply repeated the default config.

The *new* format we're going to add is another type of JSON called HAL. Here's
what's going on. We all understand the JSON format. But then, to add more *meaning*
to JSON - like certain keys that your JSON should have and their meaning - some people
come out with standards that *extend* JSON. JSON-LD is one example and HAL is
a *competing* standard. I don't often use HAL... so we're mostly doing this to
see an example of what adding a format looks like.

Oh, and the `Content-Type` for HAL is supposed to be `application/hal+json`:

[[[ code('f970655b10') ]]]

As soon as we do that, when we refresh... it shows *nothing*? I'm pretty sure
Symfony didn't see my new config file. Hop over here and clear the cache with:

```terminal
./bin/console cache:clear
```

Refresh again and... there we go! We now see `jsonhal`! And if we click, it
takes us to the `jsonhal` version of our API homepage!

Let's try an endpoint with this format. Click on the `GET` request, "Try it out",
and, down here, we can select which "media type" to request. Select
`application/hal+json`, hit "Execute", and... there it is!

You can see that it's JSON... and it has the same results, but it looks a bit
different. It has things like `_embedded` and `_links`... which are part of the
HAL standard... and not worth talking about right now.

By the way, the *reason* this new format worked *simply* by adding a tiny bit of
config is that the serializer already *understands* the `jsonhal` format. So when
we request with this `Accept` header, API Platform asks the serializer to serialize
*into* the `jsonhal` format... and *it* knows how to do that.

## Adding a CSV Format

Okay, let's do something that's a bit more practical. What if our dragon users
need to return the treasures in CSV format... like so they can import them into
Quickbooks for tax purposes.

Well, CSV *is* a format that Symfony's Serializer understands out of the box. We
know that we *could* add CSV right into this config file. But as an added challenge,
instead of enabling the CSV for *every* API resource in our system, let's just add
it to `DragonTreasure`.

Find the `ApiResource` attribute and, at the bottom, add `formats`. Just like with
the configuration, if we simple put `csv` here, that will remove the other formats.
To do this right, we need to list all of them: `jsonld`, `json`, `html`, and
`jsonhal`. Each of these will read the configuration to know which content type to
use. At the end, add `csv`. But because `csv` doesn't exist in the config, we
need to tell it which content type will activate this. So set it to `text/csv`.

[[[ code('5583ab3b8a') ]]]

Oh, but my editor is mad! It says:

> Named arguments order does not match parameters order

We know that each PHP attribute is a class... and when we pass arguments to the
attribute, we're actually passing *named* arguments to that class's constructor.
And, with named args, the *order* of the args doesn't matter. I actually don't think
PhpStorm should be highlighting this as a problem... but if you're annoyed like
I am, you can hit "Sort arguments" and... *there*. It moved `formats` up a little
higher, it's happy, and *we* won't have to stare at that yellow underline.

All right, head over, refresh, open up our collection endpoint and hit "Try it out".
This time, down here, select `text/csv` then... "Engage"! *Hello* CSV. *Too easy*!

Once again, this works because Symfony's Serializer *understands* the CSV format.
So *it* does all the work.

In fact, open the profiler for that request... and go down to the serializer
section. Yep! We can see that it's using the `csv` format... which activates
a `CsvEncoder`. *That's* why we get our nice results. If you needed to return
your results in a *custom* format that's *not* supported by the serializer, you
could add your *own* encoder to the system to handle that. It's *super* flexible

Next: Let's talk about *validation*!
