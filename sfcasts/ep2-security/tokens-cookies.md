# API Tokens? Session Cookies?

Join me, while we tell a tale as old as... the modern Internet: API authentication.
A topic of hype, complexity and unlikely heroes. Characters include sessions,
API tokens, OAuth, JSON web tokens! But what do we need for *our* situation?

The first thing I want you to ask is:

> Who will be using my API?

Is it your own JavaScript, or do you need to allow programmatic access? Like someone
will write a script that will use your API?

We're going to go through both of these use-cases... and each has some extra
complexities that we'll discuss along the way.

## Everything is a Token!

By the way, when you think of API authentication, you typically think of an API token.
And that's true! But it turns out that... pretty much *all* authentication is done
by *some* sort of a token. Even session-based authentication is done by sending
a cookie... which contains a unique, you guessed it, "token". It's a random string
that PHP uses to find and load the related session data on the server.

So the trick is figuring out which *type* of token you need in each situation and
how the end-user will *get* that token.

## Use-Case 1: Building for your Own JavaScript

So let's talk about that first use-case: the user of your API is your own JavaScript.

Well, before we even dive into security, make sure your frontend and your API live
on the same domain... like the *exact* same domain, not just a subdomain. Why?
Because if they live on two different domains or subdomains, you have to deal with
CORS: Cross-Origin Resource Sharing.

CORS not only adds complexity to your setup, it also hurts performance. Kévin
Dunglas - the lead developer of API Platform - has a
[blog post](https://dunglas.dev/2022/01/preventing-cors-preflight-requests-using-content-negotiation/)
about this. He even shows a strategy where your frontend and backend can live
in totally different directories or repositories, but *still* live on the same domain
thanks to some web server tricks.

If you *do*, for some reason, decide to put your API and frontend on different
sub-domains, then you *will* need to worry about CORS headers and you can solve that
with NelmioCorsBundle. But, I don't recommend it.

## The case for Sessions

Anyways, back to security. If you're calling your API from your own JavaScript,
the user is probably logging in via a login form with an email and password. It
doesn't matter if that's a traditional login form or one that's built with a fancy
JavaScript framework that submits via AJAX.

And, honestly, a *really* simple way to handle this use-case is *not* with API
tokens, but with good ol' fashioned HTTP Basic authentication. Yea, where you
literally pass the email & password to each endpoint. For example, the user enters
their email and password, you make an API request to some endpoint just to make
sure it's valid, then you store that email and password in JavaScript and send it
on every single API request going forward. Your email & password works basically
like an API token.

However, this has some practical challenges, like the question of *where*
you securely store the email and password in JavaScript so you can continually
use it. This is actually a problem in *general* with JavaScript and "credentials",
including API tokens: you need to be *very* careful where you store those so that
other JavaScript on your page can't read them. There *are* solutions:
https://bit.ly/auth0-token-storage - but it adds complexity that you very likely
don't need.

So instead, for your own JavaScript, you can use a session. When you start a session
in Symfony, it returns an "HTTP only" cookie... and that cookie contains the session
id. Though, the contents of the cookie aren't really important: it could be the
session id or some sort of token you invented and are reading in Symfony. The really
important thing is that because the cookie is "HTTP only", it can't be read by
JavaScript: your JavaScript or anyone else's JavaScript. But whenever you make an
API request to your domain, that cookie's *will* come with it... and your app will
use it to log in the user.

So the API token in this situation is simply the "session id", which is stored
securely in an HTTP-only cookie. Mmm. We will code through this use case.

Oh, and by the way, one edge-case with this situation is if you have a Single Sign
On situation - an SSO. In that case, you'll authenticate with your SSO like a normal
web app. When you finish, you'll have a token, which you can then use to
either authenticate the user with a session like normal... or you can use that token
directly from your JavaScript. That's a more advanced use case that we won't go
through in this tutorial... though, we *will* talk about how to read & validate
API tokens regardless of where those tokens came from.

## Use-Case 2: Programmatic Access & API Tokens

The second big use-case for authentication is programmatic access. Some *code* will
talk to your API... besides JavaScript from inside the browser.

In this case, the API clients absolutely *will* send some sort of an API token string.
And so, you need to make your API able to read a token that's sent on each request,
usually sent on an `Authorization` header:

```php
$response = $thhpClient->request(
    'GET',
    '/api/treasures',
    [
        'Authorization' => 'Bearer '.$apiToken,
    ],
);
```

*How* the user gets this token depends: there are kind of two main cases. The first
one is the "GitHub personal access token" case. This is where a user can browse
to a page on your site and click to create a new access token. Then they can
copy that and go use it in some code.

The second big case is OAuth, which is just a fancy & secure way to *get* an access
token. It's especially important when the "code" that's making the API requests is
making those requests on "behalf" of some user on your system.

Like imagine a site - ReplyToAllCommentsWithHearts.com - that  allows you to connect
with GitHub. Once you do, that site can *then* make API requests to GitHub for your
account, like making comments as your user. Or imagine an iPhone app where, to log
in, you show the user the login form on your site. Then, via an OAuth flow, that
mobile app will receive an access token it can use to talk to your API on behalf
of that user.

We're going to talk about the personal access token method in this tutorial, including
how to read and validate API tokens, no matter where they come from. We won't talk
about the OAuth flow... and it's partially because it's a separate beast. Yes, if
you have the use-case where you need to allow third parties to get API tokens
for different users on your site, you *will* need some sort of OAuth server, whether
you build it yourself or use some other solution. But once the OAuth server has done
its work, the client that will talk to your API receives... a token! And then they'll
use that token to talk to your API. So your API will need to read, validate, and
understand that token, but it doesn't care *how* the API client got it.

Ok, let's put all this theory behind us and start going through the first use-case
next: allowing our JavaScript to log in by sending an AJAX request.
