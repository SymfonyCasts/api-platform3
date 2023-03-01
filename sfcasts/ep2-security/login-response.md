# On Authentication Success

If you refresh this page and check the web debug toolbar, you can see that we're
*not* currently logged in. Let's try using a real email and password. We can cheat by
clicking the email and password links: this exists in our `AppFixtures`, so it
*should* work. And... okay... the boxes disappear! But nothing else happens. We'll
improve that in a minute.

## Thanks Session!

But for now, refresh the page and look at the web debug toolbar again. We're
*authenticated*! Just by making a successful AJAX request to that login endpoint,
that was enough to create the session and keep us logged in. Even better, if we
started making requests to our API from JavaScript, those requests would be
authenticated too. That's right! We don't need a fancy API token system where we
attach a token to every request. We can just make a request and through the magic
of cookies, that request will be authenticated.

## REST and What Data to Return from our Authentication Endpoint?

Okay, logging in *worked*... but nothing happened on the page. So... what *should*
we do after authentication? Once again, it doesn't really matter. If you're writing
your authentication system for your own JavaScript, you should do whatever is useful
for your frontend. We're currently returning the `user` id. But we *could*, if we
wanted to, return the entire `user` object as JSON.

*But* there's one tiny problem with that. It's not super RESTful. This is one of
those "REST purity" things. Every URL in your API, on a technical level, represents
a different resource. This represents the *collection* resource, and this URL
represents a *single* `User` resource. And if you have a different URL, that's
understood to be a *different* resource. The *point* is that, in a perfect world,
you would just return a `user` resource from a single URL instead of having five
*different* endpoints to fetch a user.

My point is: if we return the `User` JSON from this endpoint, we're basically creating
a new API resource that is *also* the user. In fact, *anything* we return from this
endpoint, from a REST point of view, becomes a new resource in our API. Ok, to be
honest, this is all technical semantics and you should feel free to do whatever you
want. But, I *do* have a fun suggestion.

## Returning the IRI

To try to be helpful to our frontend *and* to be somewhat RESTful, I have another
idea. What if we return *nothing* from the endpoint.... but sneak in the user's IRI
onto the `Location` header of the response. Then, our frontend could use that to
know *who* just logged in.

Let me show you. First, instead of returning the User ID, we're going to return the
IRI, which will look something like `/api/user/{id}`. But I don't want to hard code
that because we could potentially change the URL in the future. I'd rather have API
Platform *generate* that *for* me.

And fortunately, API Platform gives us an autowireable service to do that! Before
the optional argument, add a new argument type-hinted with `IriConverterInterface`
and call it `$iriConverter`. Then, down here, we're going to `return new Response()`
(the one from HttpFoundation) with *no* content and a `204` status code. The `204`
means it was "successful, but there's no content to return". We'll also pass a
`Location` header set to `$iriConverter->getIriFromResource()`. So you can get the
resource from an IRI or the IRI string from the resource, the resource being your
object. So, pass this `$user`.

## Using the IRI in JavaScript

How nice is that? Now that we're returning this, the *next* question is: How can
we use this in JavaScript? Ideally, after we log in, we would automatically show
some user info over on the right. This area is being built by another Vue file called
`TreasureConnectApp.vue`. I won't go into the details, but as long as that component
has user data, it will print it out here. *And* `LoginForm.vue` is *already* set
up to *pass* that user data to `TreasureConnectApp.vue`. Down at the bottom, after
a successful authentication, *this* is where we clear the `email` and `password`
state. That's clears the boxes after we log in. Then, if we emit an event called
`user-authenticated` and pass it the `userIri`, `TreasureConnectApp.vue`
is *already* set up to *listen* to this event. It will then make an AJAX request to
`userIri`, get JSON back, and populate its own data.

If you're not comfortable with Vue, that's ok. The *point* is that all we need to
do is grab the IRI string from the `Location` header, emit this event, and everything
else should work.

To *read* the header, we can say `const userIri = response.headers.get('Location')`.
I'll also uncomment this so we can `emit` it.

This should be good! So let's move over and refresh. The first thing I want you to
notice is that we're still logged in, but our page doesn't actually *know* we're
logged in. We're going to fix that in a minute. Go ahead and log in again using our
valid email and password. And... *beautiful*! We made the `POST` request, it returned
the IRI and then our JavaScript made a *second* request *to* that IRI to fetch the user
data, which it displayed over here.

Next: Let's talk about what it means to log *out* of an API. Then, I'll show you a
simple way of telling your JavaScript *who* is logged in on page load. Because, right
now, even though we're logged in, as soon as I refresh, our JavaScript thinks we're
logged out. Lame.
