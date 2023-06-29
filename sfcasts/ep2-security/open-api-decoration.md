# Customizing the OpenAPI Docs

To use API tokens in Swagger, we need to type the word "Bearer" and *then* the
token. Lame! Especially if we intend for this to be used by real users. So how can
we fix that?

## The OpenAPI Spec is the Key

Remember that Swagger is *entirely* generated from the OpenAPI spec document that
API Platform builds. You can see this document either by viewing the page source -
you can see it all right there - or by going to `/api/docs.json`. A few minutes
ago, we added some config to API Platform called `Authorization`:

[[[ code('79ef0de195') ]]]

The end result is that it added these security sections down here. Yup, it's that
simple: this config triggered these new sections in this JSON document: nothing else.
Swagger then reads that and knows to make this "Authorization" available.

So I did some digging directly on the OpenAPI site and I found out that it *does*
have a way to define an authentication scheme where you do *not* need to pass the
"Bearer" part manually. Unfortunately, unless I'm missing it, API Platform's
config does *not* support adding that. So are we done for? No way!
And for an *awesome* reason.

## Creating our OpenApiFactory

To create this JSON document, internally, API Platform creates an `OpenApi` object,
populates all this data onto it and then sends it through Symfony's serializer.
This is important because we can *tweak* the `OpenApi` object *before* it goes
through the serializer. How? The `OpenApi` object is created via a core
`OpenApiFactory`... and we can *decorate* that.

Check it out: over in the `src/` directory, create a new directory called
`ApiPlatform/`... and inside, a new PHP class called `OpenApiFactoryDecorator`.
Make this implement `OpenApiFactoryInterface`. Then go to "Code"->"Generate" or
`Command`+`N` on a Mac to implement the one method we need: `__invoke()`:

[[[ code('cc243e4b14') ]]]

## Hello Service Decoration!

Right now, a core `OpenApiFactory` service exists in API Platform that
creates the `OpenApi` object with all this data on it. Here's our sneaky plan:
we're going to tell Symfony to use *our* new class as the `OpenApiFactory`
*instead of* the *core* one. But... we definitely do *not* want to re-implement
*all* of the core logic. To avoid that, we'll *also* tell Symfony to pass us the
original, *core* `OpenApiFactory`.

You might be familiar with what we're doing. It's *class decoration*: an object-oriented
strategy for extending classes. It's *really* easy to do in Symfony and API Platform
leverages it a lot.

Whenever you do decoration, you will always create a constructor that accepts the
*interface* that you're decorating. So `OpenApiFactoryInterface`. I'll call this
`$decorated`. Oh, and let me put `private` in front of that:

[[[ code('4aca171c9f') ]]]

Perfect.

Down here, to start, say `$openApi = $this->decorated` and then call the `__invoke()`
method passing the same argument: `$context`:

[[[ code('50e9473a57') ]]]

That will call the core factory which will do *all* the hard work of creating
the full `OpenApi` object. Down here, return that:

[[[ code('b87699742c') ]]]

And in between? Yup, that's where *we* can *mess* with things! To make sure this
is working, for now, just dump the `$openApi` object:

[[[ code('01bee687b9') ]]]

## The #[AsDecorator] Attribute

At this moment, from an object-oriented point of view, this class *is* set up correctly
for decoration. But Symfony's container is still set up to use the *normal*
`OpenApiFactory`: it's not going to use *our* new service at all. We somehow need
to tell the container that, first, the core `OpenApiFactory` service should be
*replaced* by *our* service, and second, that the *original* core service should
be passed *to* us.

How can we do that? Above the class, add an attribute called `#[AsDecorator]` and
hit tab to add that `use` statement. Pass this the service id of the original, core
`OpenApiFactory`. You can do some digging to find this or usually the documentation
will tell you. API platform actually *documents* decorating this service, so right
in their docs, you'll find that the service id is `api_platform.openapi.factory`:

[[[ code('ca759ab7bc') ]]]

That's it! Thanks to this, anyone that was previously using the core
`api_platform.openapi.factory` service will receive *our* service instead.
But the original one will be passed to us.

So... it should be working! To test it, head to the API homepage and refresh.
Yes! When this page loads, it renders the OpenAPI JSON document in the background.
The dump in the web debug toolbar proves that it hit our code! And check out that
beautiful `OpenApi` object: it has everything including `security`, which matches
what we saw in the JSON. So now, we can tweak that!

## Customizing the OpenAPI Config

The code I'll put here is a bit specific to the `OpenApi` object and the exact
config that I know we need in the final Open API JSON:

[[[ code('4491bc450c') ]]]

We fetch the `$securitySchemes`, and then override `access_token`. This matches
the name we used in the config. Set that to a new `SecurityScheme()` object
with two named arguments: `type: 'http'` and `scheme: 'bearer'`:

[[[ code('3e6f7237ef') ]]]

That's it! First refresh the raw JSON document so we can see what this looks like.
Let me search for "Bearer". There we go! We modified what the JSON looks like!

What does Swagger think about this new config? Refresh and hit "Authorize". Ok
cool: `access_token`, `http, Bearer`. Go steal an API token... paste *without*
saying `Bearer` first and hit "Authorize". Let's test the same endpoint. Whoops, I
need to hit "Try it out". And... gorgeous! Look at that `Authorization` header!
It passed `Bearer` *for* us. Mission accomplished.

By the way, you might think, because we're completely overriding the
`access_token` config, that we could just delete it from `api_platform.yaml`.
Unfortunately, for subtle reasons that have to do with how the security
documentation is generated, we *do* still need this. But I'll say
`# overridden in OpenApiFactoryDecorator`:

[[[ code('165c605914') ]]]

This was just *one* example of how you could extend your Open API spec doc. But
if you ever need to tweak something else, now you know how.

Next, let's talk about scopes.
