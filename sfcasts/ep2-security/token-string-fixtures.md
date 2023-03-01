# Generating the API Token & Fixtures

The most important property on `ApiToken` is the token string... which needs to
be something random. Create a construct method add a `string $tokenType` argument.
This is optional, but one of the things that GitHub has started doing, since
they have multiple *types* of tokens, like personal access tokens and OAuth tokens,
is to give each token type a different prefix. It just helps figure out where each
comes from.

We're only going to have one token type, but we'll follow the idea. On top, to
store the type prefix, add `private const PERSONAL_ACCESS_TOKEN_PREFIX = 'tcp_`.
I just made up that prefix. Our site is called Treasure Connect... and this is a
personal access token, so `tcp_`.

Now, below, for `string $tokenType =` default it to
`self::PERSONAL_ACCESS_TOKEN_PREFIX`. For the token itself, say
`$this->token = $tokenType.` and then I'll use some code that will generate a random
string that is 64 characters long. So that's 64 characters here plus the 4 character
prefix equals 68. That's why I chose that length. And because we're setting the
`$token` in the constructor, this doesn't need to `= null` or be nullable anymore.
It will always be a `string`.

## Setting up the Fixtures

Ok! This is now set up. Now let's add some API tokens to the database. At your
terminal, run

```terminal
php bin/console make:factory
```

so we can generate a Foundry factory `ApiToken`. Go check out the new class:
`src/Factory/ApiTokenFactory.php`. Down in `getDefaults()`... this looks mostly
fine, though we don't need to pass in the `token`. Oh, and I want to tweak scopes.

Pretty commonly, when you create an access token - whether it's a personal access
token or one created through OAuth, you're able to *choose* which permissions
that token will have: it does *not* automatically have all the permissions that
a normal user would. I want to add *that* into our system as well.

Back over in `ApiToken`, at the top after the first constant, I'm going to paste
a few more constants. This defines three different scopes that a token can have.
This isn't all the scopes we could imagine, but it's enough to make things realistic.
So, when you create a token, you can choose whether that token should have permission
to edit user data, or whether it can create treasures on behalf of the user or whether
it can edit treasures on behalf of the user. I also added a `public const SCOPES`
to describes them.

Back over in our `ApiTokenFactory`, let's, by default, give each `ApiToken` two
of those three scopes.

Ok! `ApiTokenFactory` is ready. Last step: open `AppFixtures` so we can *create*
some `ApiToken` fixtures. What I basically want to do is make sure that, in our dummy
data, each user has at least one or two API tokens. An easy way to do that, down
here is to say `ApiTokenFactory::createMany()`. Since we have 10 users, let's create
30 tokens. Then pass that a callback function and, inside, return an override for
the default data. We're going to override the `ownedBy` to be `UserFactory::random()`.

So this will create 30 tokens and assign them randomly to the 10, well really
11 users in the database. So on average, each user should have about three API tokens
already assigned to them. I'm doing this because, to keep things simple, we're not
going to build a user interface where the user can *actually* click and create
access tokens and select the scopes. We're going to skip all that. Instead, since
every user will already have some API tokens in the database, we can jump straight
to learning how to read and *validate* these in our API tokens.

Reload our fixtures with:

```terminal
symfony console doctrine:fixtures:load
```

## Showing the Tokens on the Frontend

And... beautiful! But since we're *not* going to build an interface for creating
API tokens, we need an easy way to *see* the API tokens for my user... so we can
test them in our API. When we're authenticated, we can show them right here.

This isn't a very important detail, so I'll do it real quick. Over in `User`,
at the bottom, I'll paste in a function called `getValidTokenStrings()` that returns
an array of the valid APO token strings for this user. In `ApiToken`, we also
need an `isValid()` method... so I'll paste that as well. You can get all of this
code from the code blocks on this page.

Next, open up `assets/vue/controllers/TreasureConnectApp.vue`... and add a new
prop that can be passed in: `tokens`. Thanks to that, we'll have a new `tokens`
variable in the template. After the "Log Out" link, I'll paste in some code that
will render those.

Last step: open `templates/main/homepage.html.twig`. This is where we're passing
props to our Vue app. Pass a new one called `tokens` set to, if `app.user`, then
`app.user.validTokenStrings`, else `null`.

Let's try this! If we refresh, right now we are not logged in. Use our cheater
links to log in. Notice that it doesn't show them yet... we could improve thing
to do that... but right now, after you refresh... *there* they are. We have
*two* tokens.

Next: let's write a system so that can *read* these tokens and authenticate the
user instead of using session authentication.
