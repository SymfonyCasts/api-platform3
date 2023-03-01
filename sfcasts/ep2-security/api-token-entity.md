# Token Types & The ApiToken Entity

Okay, so what if you need programmatic access to your API?

## The Types of Access Tokens

Well, one way or another, when you talk to an API via code, you'll send an API token,
commonly known as an access token. Exactly how you *get* that token will vary.
But there are two main cases.

First, as a user on the site, like a dragon, you want to generate an API token so
that you can personally use it in a script you're writing. This is Like a GitHub
personal access token. These are literally created via a web interface. We're going
to show this use-case.

The second main use case is when a third party wants to make requests to your API
on *behalf* of a user of your system. Like some new site called
`DragonTreasureOrganizer.com` wants to be able to make an API request to *our*
API on behalf of some of our users - like it will fetch the treasure's for a user
and display them decoratively. In this situation, instead of our users generating
their tokens manually and then... like... entering them into that site, you'll use
OAuth. OAuth is basically a mechanism for normal users to securely give access
tokens for their account to a third party. And so, your site, or somewhere in your
infrastructure you'll have an OAuth server.

That's beyond the scope of this tutorial. But the important thing is that after OAuth
is done, the API client wll end up with, you guessed it, in API token! So no matter
which path you're in, if you're doing programmatic access, your API users will end
up with an access token. And then *your* job will be to read and understand that.
We'll do *exactly* that.

## JWT vs Database Storage?

So as I mentioned, we're going to show a system where we allow users to generate
their own access tokens. So how do we do that? Again, there are two main ways. Oh,
death by choices!

The first is to generate something called a JSON web token or JWT. The cool thing
about JWTs are that they don't need to be stored in a database. They're special
strings that actually *contain* all the info inside of them. For example, you
could create a JWT string that includes the user id and some scopes.

One *downside* of JWTs are that there's no easy way to "log out"... because there's
no out-of-the-box for to invalidate JWTs. You give them an expiration when you
create them... but then they're valid until then, no matter what.

JWT's are trendy, popular and fun! But... you may not need them. They're super great
when you have a single sign-on system because, if that JWT is used to authenticate
with multiple systems or APIs, each API can validate the JWT all on their own:
without needing to make an API request to some central authentication system.

So you might end up using JWTs and there's a great bundle for it called
LexikJWTAuthenticationBundle in Symfony. JWT's are also the type of access token
that OpenID gives you in the end.

Instead of JWTs, the second main option is dead simple: generate a random token string
and store it in the database. This also allows you to invalidate access tokens by...
just deleting them! This is what we'll do.

## Generating the Entity

So let's get to it. To store API tokens, we need a new entity! Find your terminal
and run:

```terminal
php bin/console make:entity
```

And let's call the new entity `ApiToken`. Say no to making this an API resource.
In theory, you *could* allow users to authenticate via a login form or HTTP basic
and then send a POST request to create API tokens... though you probably wouldn't
want operations to fetch those tokens.

Anyways, add an `ownedBy` property. This is going to be a `ManyToOne` to `User` and
not `nullable`. And I'll say "yes" to the inverse. So the idea is that every `User`
can have many API tokens. So if I use an API token, we want to know which `User`
it's related to. We'll use that during authentication. Calling the property
`apiTokens` is fine and say no to orphan removal. Next property: `expiresAt`, make
that a `datetime_immutable` and I'll say yes to `nullable`. Maybe we allow tokens
to *never* expire by leaving this field blank. Next up is `token`, which will be
a string. I'm going to the length to 68 - I'll tell you why in a minute - not
`nullable`. And finally, let's add a `scopes` property as a `json` type. This is
going to be kind of cool: we'll store an array of "permissions" that this API token
should have. Say, not `nullable` on that one as well. Finally, hit enter to finish.

All right, spin over to the editor. No surprises: that created an `APIToken` entity...
and there's nothing too interesting right now. So let's go over and make the migration
for It:

```terminal
symfony console make:migration
```

Then... spin over and peek at that file to make sure it looks good. Yup! It creates
the `api_token` table. Run that with

```terminal
symfony console doctrine:migrations:migrate
```

And... awesome! Next: let's add a way to generate the random token string. Then,
we'll talk about scopes and load up our fixtures with some API tokens.
