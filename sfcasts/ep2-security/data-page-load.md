# Logout & Passing API Data to JavaScript

What does it actually mean to log out of something? Like logging out of an api? Well,
it's two things. First, it means invalidating whatever your token is, if possible.
For example, if you have an API token, you would say to the API:

> Make this API token no longer valid.

In the case of session authentication, it's basically the same: it means removing
the session from the session storage.

The second part of "logging out" is making whoever is using the token "forget" it.
If you had an API token and you were using it in JavaScript, you would *remove* it
from JavaScript. For session authentication, it means deleting the cookie.

## Adding the Ability to Log Out

Anyways, let's add the ability to log out of our session authentication. So back
over in `SecurityController`, like before, we need a route and controller, even though
this controller is never going to be called. I'll call the method `logout()`, we're
actually going to return `void`. You'll see why in a second. And then I'll give it
a `Route` of `/logout` and `name: app_logout`.

The reason that I chose `void` is because we're going to throw an exception from
inside the method. We've created this *entirely* because we need a route... but
Symfony's security system will intercept things before the actual controller is called.

To activate that, in `security.yaml`, add a key here called `logout` with `path`
below set to that new route name: `app_logout`. So this activates a listener that's
now watching for requests to `/logout`. When there *is* a request to `/logout`,
it logs the user out and redirects them.

All right, over here, our Vue app thinks we're not logged in, but we *are* and we
can see it in the web debug toolbar. So if we manually go to `/logout`... boom!
We *are* logged out.

## Getting the Current User Data in JavaScript

So we saw a moment ago that even when we *are* logged in and refresh, our Vue app
has no *idea* that we're logged in. So how could we fix that? One idea would be
to create a `/me` API endpoint. Then, on load, our Vue app could make an AJAX request
to that endpoint... which would either return `null` or the current user information.
But, `/me` endpoints are super *not* RESTful. And there's a better way anyways:
dump the user information into JavaScript on page load.

## Setting a Global user JavaScript Variable

There are two different ways to do this. The first is by setting a global variable.
For example, in `templates/base.html.twig`, it doesn't really matter where, but
inside the body, add a `script` tag. And here we'll say `window.user =` and then
`{{ app.user|serialize }}`. Serialize into `jsonld` and a `|raw` so that it doesn't
output escape the output;: we want raw JSON.

How cool is that? In a minute, we'll read that from our JavaScript. If we refresh
right now and look at the source, yea! We see `window.user = null`. And then when
we log in and refresh the page, check it out: `window.user =` and then a huge amount
of data!

## Serializing to JSON-LD in Twig

But there's something mysterious going on: it has the correct fields! Look closely,
it has `email`, `username` and then `dragonTreasures`, which is what all this stuff
is. It also, correctly, does *not* have `roles` or `password`.

So it seems that it's correct reading our normalization groups! But how is that even
possible? We're just saying "serialize this user to `jsonld`". This has nothing to
do with API Platform and it's not being processed by API platform. But... our
normalization groups are *configured* in API Platform. So how did the serializer
know to use those?

The answer to that, as best I can tell, is that it's working... partially by accident.
During serialization, API Platform sees that we're serializing an "API resource"
and so it looks up the metadata for this class.

That's cool... but it's actually *not* perfect... and I like to be explicit anyways.
Pass a 2nd argument to serialize, which is the context and set `groups` to
`user:read`.

Now, watch what happens when we refresh. Like before, the correct properties on
`User` will be exposed. But watch the embedded `dragonTreasures` property. Woh,
it changed! That was actually *wrong* before: it was including *everything*, not
just the stuff inside the `user:read` group.

## Reading the Dynamic Data from Vue

Ok, let's go use this global variable over in JavaScript - in
`TreasureConnectApp.vue`. Right now, the `user` data always starts as `null`. We
can change that now too `window.user`.

When I refresh... got it!

Next: if you're using Stimulus, an even better way to pass data to JavaScript is
to use Stimulus values.
