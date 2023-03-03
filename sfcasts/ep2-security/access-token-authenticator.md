# Access Token Authenticator

To authenticate with an API token, an API client will send an `Authorization` header
set to the word `Bearer` then the token string... which is just a standard practice.
Then something in our app will *read* that header, make sure the token is valid,
and then authenticate the user.

## Activating access_token

And fortunately, Symfony has a built-in system for this! Spin over and open up
`config/packages/security.yaml`. Anywhere under your firewall add `access_token`.

This activates a listener that will watch every request to see if it has an
`Authorization` header. If it does, it will read that and try to authenticate
the user.

Though, it needs another class to help, because even though it knows where to
*find* the token on the request... it has no idea what to do with! It doesn't
know if it's a JWT or, in our case, that it can query the database for the matching
record. So to help it, add a `token_handler` option set to the id of a service
we'll create: `App\Security\ApiTokenHandler`.

## Stateless Firewall

By the way, if your security system only allows authentication via an API
token, then you don't need session storage. If you have that situation, you can
set a `stateless: true` flag. That tells the security system that if a user logs
in, don't bother storing the user info in the session. I'm going to remove that,
because we *do* have a way to log in that relies on the session.

## The Token Handler Class

Anyway, let's go create that handler class. In the `src/` directory create a new
sub-directory called `Security/` and inside of that a new PHP class called
`ApiTokenHandler`. This is a beautifully simple class. Make it implement
`AccessTokenHandlerInterface` and then go to Code -> Generate or Command + N on a
Mac and select "Implement Methods" to generate the one we need:
`getUserBadgeFrom()`.

The `access_token` system knows how to *find* the token: it knows it will live
on an `Authorization` header with the word `Bearer` in front of it. So it grabs
that string then calls `getUserBadgeFrom()` and passes it to us. By the way this
`#[\SensitiveParameter]` attribute is new feature in PHP. It's not important,
it just makes sure that an exception thrown, this value won't be shown in the
stacktrace.

Ok: our job here is to query the database using the `$accessToken` and then return
which *user* it relates to. To do that, we need the `ApiTokenRepository`! Add
a construct method up here and with a
`private ApiTokenRepository $apiTokenRepository` argument. Below, say
`$token = $this->apiTokenRepository` and then use the `findOneBy()` passing it an
array so it will query where the `token` field equals `$accessToken`.

If authentication should fail for *any* reason here we need to throw a type of
*security* exception. For example, if the token doesn't exist, throw a new
`BadCredentialsException`: the one from Symfony components. That will cause
authentication to fail... and we don't need to pass a message. This will return
a "Bad Credentials"  message to the user.

At this point, we *have* found the `ApiToken` entity. Ultimately our security system
wants to log in a *user*... not an "Api Token". We do that by returning a
`UserBadge` that, sort of, wraps the `User` object. Watch: return a `new UserBadge()`.
The first argument is the "user identifier". Pass `$token->getOwnedBy()` to get the
`User` and then `->getUserIdentifier()`.

## How the User Object is Loaded

Notice that we're not *actually* returning the `User` object. That's mostly just
because... we don't need to! Let me explain. Hold command or ctrl and click
`getUserIdentifier()`. What this *really* returns is the user's `email`. So we're
returning a `UserBadge` with the user's `email` inside. What happens next is the
same thing that happens when we send an `email` to the `json_login` authentication
endpoint. Symfony's security system takes that email and, because we have this user
provider set up, it's knows to query the database for a `User` with that `email`.

So it's going to query the database *again* for the `User` via the email... which
is a bit unnecessary, but fine. If you want to avoid that, you could pass a
callable as a second argument and return `$token->getOwnedBy()`. But this will
work fine as it is.

Oh, and we should probably check to see if the token is valid! If not
`$token->isValid()`, then we could throw another `BadCredentialsException`.
But if you want to customize the message, you can also throw a new
`CustomUserMessageAuthentication` exception and then say "Token expired" to
return *that* message to the user.

## Using the Token in Swagger?

And... done! So... how do we try this? Well, ideally we could try it via our Swagger
docs. I'm going to open up a new tab... then log out. But I'll keep my original tab
open... just so I can steal the valid API tokens for the user.

Head to the API docs. The question is: how can we tell this interface to send an
API token when it makes the requests? Well you may have noticed an "Authorize"
button. But when we click it... it's empty! That's because we haven't, yet, told
Open API *how* users are able to authenticate. Fortunately we can do this via API
Platform.

Open up `config/packages/api_platform.yaml`. And a new key called `swagger`.
And then we're going to add a new way of authenticating. Set `api_keys` to activate
that type, then `access_token`... which is just a name for this that I made up.
Below this, give this authentication mechanism a name... `type: header` because
we want Swagger to pass the token as a header.

This will tell Swagger - via our OpenAPI docs - that we can  we can send API tokens
via the `Authorization` header. *Now* when we click the "Authorize" button...
perfect! It says "Name: Authorization", "In Header".

To use this, we need to start with the word `Bearer` then a space... because it
doesn't fill that in for us. More on that in a minute. Let's first try an invalid
token. Hit "Authorize". That didn't actually make any requests yet. It just stored
the token in JavaScript.

Let's try the get treasure collection endpoint. When we execute... awesome! A
401! We don't *need* to be authenticated to use this endpoint, but because we passed
an `Authorization` header with `Bearer` and then a token, our new `access_token`
system caught that, passed the string to our handler... but then we couldn't find
that so, so we threw the `BadCredentialsException`

You can see it down here: the API returned an empty response, but with a header
containing `invalid_token` and `error_description`: "Invalid credentials.".

## Checking the Token Authentication is Working

So the *bad* case is working. Let's try the happy case! In the other tab, copy
one of the valid API tokens. Then slide back up, hit "Authorize", then "Log out".
Logging out just means that it "forgets" the API token that we set there a minute
ago. Re-type `Bearer `, paste, hit "Authorize", close... and let's go down and try
this endpoint again. And... woohoo! A 200 status code.

So it *seems* like that worked... but how can we tell? Whelp, down on the web
debug toolbar, click to open the profiler for that request. On Security tab...
yes! We are logged in as Bernie. Success!

The only thing I *don't* like is needing to type that `Bearer` string in the
authorization box. That's not very user-friendly. So next, let's fix that by
learning how we can customize the OpenAPI spec document that Swagger uses.
