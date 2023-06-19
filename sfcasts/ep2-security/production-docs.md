# API Docs on Production?

Welcome back you wonderful JSON-returning people, to API Platform episode 2. In
[part 1](https://symfonycasts.com/screencast/api-platform), we got busy!
We created a pretty killer API to store dragon treasures,
though... we completely forgot to add security! Any small, hairy-footed creature
could sneak in a back door... and we'd have absolutely no idea! So this time,
we're talking *everything* related to security. Like authentication: should I
use a session with a login form... or do I need API tokens? And authorization,
like denying access to entire endpoints. Then we'll get into trickier things
like showing or hiding results based on the user and even showing or hiding
certain *fields* based on the user. We'll also talk about totally custom fields,
the PATCH HTTP method and setting up an API test system your friends will be
jealous of.

## Project Setup

Now, you know the drill: to *really* dig into this stuff, you should code
along with me. Download the code course code from this page. After you unzip it,
you'll find a `start/` directory with the same code that you see here. Pop open
this nifty `README.md` file and go through all the setup instructions.

I'm all the way down here at starting the `symfony` web server. So I'll spin over
to a terminal that's already inside the project and run

```terminal
symfony serve -d
```

to start a local web server in the background. Perfect! I'll hold `Cmd` and click
that URL to pop that open in my browser. Hello Treasure Connect! This is
the app we created in episode 1... though we worked exclusively on the API. We
created endpoints for treasures, users *and* the ability to relate them.

This homepage is brand new for episode 2. It's a small Vue app that I built. It
has a login form... but it doesn't work yet: it will be up to *us* to bring it
to life.

## Interactive Docs on Production?

Now before we dive into security, one question I sometimes get is:

> Hey Ryan, the interactive docs are super cool... but could I hide them on
> production?

If your API is private - it's just meant for your JavaScript - that might make
sense because you don't want to advertise your endpoints to the world. However,
I don't feel *too* compelled to hide the docs... because even if you do, the
endpoints *still* exist. So you're going to need proper security anyways.

But yes, hiding them is possible, so let's see how. Even if you *will* show your
docs, this is kind of an interesting process that shows how various parts of the
system work together.

Find your terminal and run:

```terminal
php ./bin/console config:dump api_platform
```

Remember: this command show all the *possible* configuration for API Platform.
Let's see... search for "swagger". There we go. There's a section with things like
`enable_swagger`, `enable_swagger_ui`, `enable_re_doc`, `enable_entrypoint`, and
`enable_docs`. What does all that mean?

## Hello ReDoc

First I want to show you what ReDoc is, because we didn't talk about that in the
first tutorial. We're currently looking at the Swagger version of our
documentation. But there's a competing format called ReDoc... and you can
click on the "ReDoc" link at the bottom to see it! Yup! This is the *same*
documentation info... but with a different layout! If you like this, it's there
for you.

## Disabling The Docs

Anyways, back at the terminal, there are a lot of "enable" configs. They're all
related... but slightly different. For example, `enable_swagger` really refers to
the OpenAPI documentation. Remember that's the JSON document that powers the Swagger
and ReDoc API docs. Then, these are whether or not we want to *show* those two types
of documentation frontends. And down here, `enable_entrypoint` and `enable_docs`
control whether or not certain *routes* are added to our app.

I bet that didn't *completely* make sense, so let's play with this. Pretend
that we want to disable the docs entirely. Ok! Open `config/packages/api_platform.yaml`
and, to start, add `enable_docs: false`:

[[[ code('014b51d152') ]]]

As soon as you do that and refresh... alright! Our API documentation is gone... but
with a 500 error. When you `enable_docs: false`, it literally removes the *route*
to our documentation.

Let's back up. Going to `/api` was always kind of a shortcut to get to the docs.
The *real* path was `/api/docs`, `/api/docs.json` or `.jsonld`. And these *are*
now all 404's because we disabled that route. So yay our documentation is gone!

However, when you go to `/api`, this actually *isn't* a documentation page. This
is known as the "entry point": it's our API homepage. This page *does* still
exist... but it tries to *link* to our API docs... which don't exist, and it explodes.

To disable the entry point, move over and add `enable_entrypoint: false`:

[[[ code('9af27a9478') ]]]

Now going to `/api` give us... beautiful! A 404.

Ok, so we know we can go to `/api/treasures.json` or `.jsonld`. But what if
we just go to `/api/treasures`? That...  unfortunately is a 500 error! When our
browser makes a request, it sends an `Accept` header that says that we want HTML.
So we're asking our API for the `html` version of the treasures. And the `html`
version is... the documentation. So it tries to link to the documentation and
explodes.

To disable this, we can communicate to the system that we don't have Swagger or API
documentation at all... so it should stop trying to link to it. Do that by setting
`enable_swagger: false`:

[[[ code('e6d40ceaa4') ]]]

Though... that just trades for another 500 error that says:

> Hey, you can't enable Swagger UI without enabling Swagger!

Fix that with `enable_swagger_ui: false`:

[[[ code('78d4a3c223') ]]]

And now... closer!

## Disabling the HTML Format

> Serialization for the format `html` is not supported.

The problem is that we're *still* requesting the `html` version of this resource.
But now that we don't have any documentation, our API is like:

> Um... not really sure how to return an HTML version of this.

And the truth is: if we totally disable our docs, we don't *need* an HTML format
anymore! And so, we can disable it. Do that by, very simply, removing `html` from
`formats`:

[[[ code('c876501d2c') ]]]

And... we actually have one other spot where we need to do that: in
`src/Entity/DragonTreasure.php`. When we added our custom `csv` format... let's see
here it is... we repeated all the formats including `html`. So take `html` off
of there as well:

[[[ code('2eaff81582') ]]]

When we refresh now... got it! Since there's no HTML format, it defaults to `JSON-LD`.
Our docs are now totally disabled.

Oh, and to disable the docs *just* for production, I would create an environment
variable - like `ENABLE_API_DOCS` - then reference that in my config:

***TIP
Actually, due to how the config is loaded, environment variables won't work here!
Instead, you could disable docs in production only, via:

```yaml
when@prod:
    api_platform:
        enable_swagger_ui: false
```
***

```yaml
# config/packages/api_platform.yaml
api_platform:
    enable_swagger_ui: '%env(bool:ENABLE_API_DOCS)%'
```

But... I *do* like the documentation, so I'm going to undo this change... and
this change as well to get our docs back.

[[[ code('7e052a8094') ]]]

[[[ code('66b2f1c986') ]]]

Love it!

Next, let's have a fireside chat about authentication. You have a fancy API:
do you need API tokens? Or something else?
