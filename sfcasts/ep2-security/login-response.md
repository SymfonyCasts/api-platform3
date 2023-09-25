# On Authentication Success

If you refresh the page and check the web debug toolbar, you can see that we're
*not* logged in. Let's try using a real email and password. We can cheat by
clicking the email and password links: this user exists in our `AppFixtures`, so
it *should* work. And... okay... the boxes disappear! But nothing else happens.
We'll improve that in a minute.

## Thanks Session!

But for now, refresh the page and look at the web debug toolbar again. We're
*authenticated*! Yea! *Just* by making a successful AJAX request to that login
endpoint, that was enough to create the session and keep us logged in. Even better,
if we started making requests to our API from JavaScript, those requests would be
authenticated too. That's right! We don't need a fancy API token system where we
attach a token to every request. We can just make a request and through the magic
of cookies, that request will be authenticated.

***TIP
In new API Platform projects, the default `config/packages/api_platform.yaml` file
has configuration that makes your endpoints "stateless":

```yml
# config/packages/api_platform.yaml
api_platform:
    # ...
    defaults:
        stateless: true
```

If you want to be able to make API requests and rely in the session to stay authenticated,
change this to: `stateless: false`.
***

## REST and What Data to Return from our Authentication Endpoint?

So, logging in *worked*... but nothing happened on the page. What *should*
we do after authentication? Once again, it doesn't really matter. If you're writing
your auth system for your own JavaScript, you should do whatever is useful
for your frontend. We're currently returning the `user` id. But we *could*, if we
wanted, return the entire `user` object as JSON.

*But* there's one tiny problem with that. It's not super RESTful. This is one of
those "REST purity" things. Every URL in your API, on a technical level, represents
a different resource. This represents the *collection* resource, and this URL
represents a *single* `User` resource. And if you have a different URL, that's
understood to be a *different* resource. The *point* is that, in a perfect world,
you would just return a `User` resource from a single URL instead of having five
*different* endpoints to fetch a user.

If we return the `User` JSON from this endpoint, we're "technically" creating
a new API resource. In fact, *anything* we return from this endpoint, from a REST
point of view, becomes a new resource in our API. To be honest, this is all
technical semantics and you should feel free to do whatever you want. But, I
*do* have a fun suggestion.

## Returning the IRI

To try be helpful to our frontend *and* somewhat RESTful, I have another
idea. What if we return *nothing* from the endpoint.... but sneak the user's IRI
onto the `Location` header of the response. Then, our frontend could use that to
know *who* just logged in.

Let me show you. First, instead of returning the User ID, we're going to return the
IRI, which will look something like `'/api/users/'.$user->getId()`. But I don't want to hard code
that because we could potentially change the URL in the future. I'd rather have API
Platform *generate* that *for* me.

And fortunately, API Platform gives us an autowireable service to do that! Before
the optional argument, add a new argument type-hinted with `IriConverterInterface`
and call it `$iriConverter`:

[[[ code('4519be269a') ]]]

Then, down here, `return new Response()` (the one from `HttpFoundation`) with *no*
content and a `204` status code:

[[[ code('40e60a96c3') ]]]

The `204` means it was "successful... but there's no content to return". We'll also
pass a `Location` header set to `$iriConverter->getIriFromResource()`:

[[[ code('5c93ae32a9') ]]]

So you can get the resource from an IRI or the IRI string from the resource,
the resource being your object. Pass this `$user`.

## Using the IRI in JavaScript

How nice is that? Now that we're returning this how can we use this in JavaScript?
Ideally, after we log in, we would automatically show some user info over on the right.
This area is built by another Vue file called `TreasureConnectApp.vue`:

[[[ code('18bdec01c3') ]]]

I won't go into the details, but as long as that component has user data, it will
print it out here. *And* `LoginForm.vue` is *already* set up to *pass* that user
data to `TreasureConnectApp.vue`. Down at the bottom, after a successful
authentication, *this* is where we clear the `email` and `password` state,
which empties the boxes after we log in. If we emit an event called
`user-authenticated` and pass it the `userIri`, `TreasureConnectApp.vue`
is *already* set up to *listen* to this event. It will then make an AJAX request to
`userIri`, get the JSON back, and populate its own data.

If you're not comfortable with Vue, that's ok. The *point* is that all we need to
do is grab the IRI string from the `Location` header, emit this event, and everything
should work.

To *read* the header, say `const userIri = response.headers.get('Location')`.
I'll also uncomment this so we can `emit` it:

[[[ code('d7202bed45') ]]]

This should be good! Move over and refresh. The first thing I want you to
notice is that we're still logged in, but our Vue app doesn't *know* that we're
logged in. We're going to fix that in a minute. Log in again using our
valid email and password. And... *beautiful*! We made the `POST` request, it returned
the IRI and then our JavaScript made a *second* request *to* that IRI to fetch the user
data, which it displayed here.

Next: Let's talk about what it means to log *out* of an API. Then, I'll show you a
simple way of telling your JavaScript *who* is logged in on page load. Because, right
now, even though we are logged in, as soon as I refresh, our JavaScript thinks we're
not. Lame.
