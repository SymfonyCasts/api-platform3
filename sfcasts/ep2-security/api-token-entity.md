# Api Token Entity

Coming soon...

Okay, so what if you need programmatic access to your api?

Well, one way or another, when you talk about an api, when you talk to an API via
code, you'll send an API token exactly how you get that API token will vary. But
there are two main cases. One, as a user on the site, like a dragon, you want to
generate an API token so that you can use it in a script. You are writing like a
GitHub personal access token. These are literally created via a web interface. Then
used with the api. We are gonna show this use case. The second main use case is a
third party wants to make requests to your API on behalf of the users of your system.
Like some new site called Dragon Treasure Organizer wants to be able to make a API
request to our site on behalf of some of our users in that case. Instead, in that
case, instead of our users generating their own tokens and then like giving them to
that site, we'll use oof our site or somewhere in our infrastructure will have an
OAuth server. That's beyond the scope of this tutorial. But the important thing is
that after OAuth is done,

The API clients end up with, you guessed it, in API token. So no matter which path
you're in, if you're doing programmatic access, you end up with an API token called
an access token. All right, so as I mentioned, we're gonna show a system where we
actually gener allow the users to generate their own access tokens. So how do we do
that? Again, there are sort of two main ways. The first is to generate something
called A J S O N Web Token or J W T. The cool thing about JWTs are that they don't
need to be stored in a database. They actually contain all the information inside
them. Like you could in, you can include the user ID and scopes right inside of A J W
T. The downside of a JW T is that there's no easy way to log out because there's no
way, no way out of the box for you to invalidate JWTs. Now, JWT is a really trendy
and popular, and there's super great when you have a single sign-on system because
the different parts of the, because the multiple APIs and your and your
infrastructure can all validate those JWTs without having to make an extra a P I
request to the single sign-on system. They're self-contained and they can be
validated.

So you might end up using W JWTs and there's a great bundle for it called Lexi JWT
Bundle in Symphony. And the second main way of doing things is what we're gonna do,
it's simple, it's dead simple, is you generate a token string and you store it in the
database. This is simple and even allows you to invalidate ACC access tokens by just
deleting them from the database. Nice and simple. So let's get to it. We are gonna
have a database full of API tokens. So find your terminal and run bin console, make
entity, and let's call our new entity API token. I'm gonna say no to making this an
API resource and for the first record, I'm gonna say owned by, and this is gonna be a
many to one to user and not knowable. And I'll say yes to the inverse. So the idea is
that every user can have many API tokens. So if a use an API token, we wanna know
which user that's related to. We're gonna use that during authentication. So I'll use
the API tokens property and they'll say no to orphan removal. Next property, we're
probably gonna want an expires at,

Make that a daytime immutable. I'll say yes to knowable. Maybe we allow tokens that
don't have an expiration and the exhibition need the token. This is gonna be a
string. I'm gonna set up to be 68 characters long. I'll tell you why in a second, not
knowable. And finally, let's do a scopes property. This is gonna be kind of cool
where we'll have this be a JS O. This is gonna contain an a JSON array of scopes,
kind of permissions that this API token has. I'll say, not knowable on that one as
well. And then hit enter to finish. All right, so if you spin over, we know what that
does. It creates an API token entity. There's nothing too interesting on this right
now. So let's go over and make the migration for IT. Symphony console, make
migration. Hmm, we're on that. And then we'll spin over and just peek at that
migration. Make sure it looks good. Yeah, perfect. Creating the API token table. So
run that with symphony console. Doctor Migrations migrate. Beautiful. All right, so
the token string is something, it needs to be set to a random token, random string.
To set this, we're gonna create a construct method here.

And actually I'm gonna add a string token type, um, argument to this. So this is
optional, but one of the things that GitHub has started doing is that they will, they
have different types of tokens like personal access tokens, OA tokens, and to
differentiate those tokens, it gives them each a different prefix. It just kind of
helps you figure out where they're coming from. So we're only gonna have one token
type, and I'm gonna do the same thing here. And on top to store my one token type
string, I'm gonna say private constant per per personal access token prefix equals
TCP underscore, that's a thing I just made up our sites called Treasure connect. And
this is a personal access token. So TCP underscore, they're not here for token type.
We'll say screen token type equals, and we'll default it to self colon personal
access token prefix. With the token itself. Let's say this token equals token type
dot. And then I'm gonna use a little bit, a little code down here that's gonna
generate a random string that is 64 characters long. So if you come here, we have 64
characters here. This is gonna be four characters long, 68 characters. That's why I
chose that for our length and because we're setting the token in a constructor, this
does not need to equal null or be null of anymore. It will always be a string.

All right? Right. So this is now set up I'm gonna watch. Next, I wanna add some API
tokens into our database. So we're gonna run pin console, make factory, so we can
generate a foundry factory for that. We've done this a few times before. This now new
source factory API token factory and down and get defaults. This looks fine though. I
don't need a pass in the token. And for scopes, a lot of times when you create an API
token, you're able to control like which permissions it has. It doesn't have all the
permissions that a normal user has. We're gonna kind of create a mini system for
this. So you can see how that looks. So back over an API token, open the top after my
first constant, I'm gonna pace a couple other constants here. So what I'm doing here
is I'm defining three different scopes that a token can have. And this isn't all the
scopes we could have, but I'm just pretending that maybe we have an API token and
you're able to control whether a token can edit that user or whether it can create
treasures on behalf of the user or whether it can edit treasures on behalf of the
user. So we have those three tokens here. We put a little public scopes here that
kind of describes them.

And then back over in our API token factory, we'll just give each by the default a
fault, we'll give each uh, AP API token, two of those three scopes. All right,
awesome. So the AP API token factor is ready. Last step is let's go into our data
fixtures app fixtures and let's just create, what I basically wanna do is make sure
that in our dummy data, every user has eh one or two API tokens. An easy way to do
that down here is say API token, factory, colon, colon, create many since we have 10
users, let's create 30 tokens. Then I'm gonna pass that a little callback function
and inside of here we'll return and override for the default data. And we're just
gonna override the owned by to be user, factory, colon, colon, random. So it'll
create 30 tokens down here, assign them randomly to the 10, well really 11 users that
we have. So on average, every user should have about three API tokens already
assigned to them. I'm doing this because to keep things simple, we're not gonna build
a user interface where the user can actually click and create an access token and
select the scopes. I'm gonna skip all that. Instead, we're just gonna give all of our
users some API tokens and our fixtures so that we can jump straight into the API in
seeing how to validate those tokens. So let's reload our fixtures with Symphony
console

Doctrine, fixtures, load, and beautiful. All right, since we're not going to build an
interface on our site to actually be able to create API tokens, we're gonna need to
wait to see the API tokens that my user has so that we can use them just to kind of
play with our api. So we're gonna add a little thing here where once we're
authenticated, we print out the API tokens right there. This is not all that
important, so I'm gonna do it real quick. Over in user dot php down on the bottom,
um, I'm gonna paste in a function called get valid token strings. It just loops over
all the API tokens and then finds the ones that are valid and then returns the actual
strings. This returns an array of strings. We do need to add a little token is valid
method. So down here I'm gonna paste that as well. So if expires at as nu or if it
expires as the future, this is valid. So nice, easy way to return all of our A API
token strings. Next we're gonna pass that into our view application. So I'm gonna go
op, open up assets, view controllers, treasure connect, app dot view,

And we're now gonna pass entry point user and also tokens. And now we'll have a
tokens variable inside of here. If you go up here and find our log out link, I'm
gonna paste a little bit of code there as well, which you can also get from this
page. So pretty simple. It says tokens and then it just has a little loop right here
to loop over the tokens and print those out. So last thing we need to do is pass that
into our template, pass that into our view app. So templates main homepage, H on
Twig, this is where we're passing props to our view app. So now we can pass a new one
called tokens and can kind of cheat and say if we have a user, so if app user pass
app user dot valid token strings to use that new method, created else Pass Nu. All
right, let's try this for refresh. Right now we are not logged in no API tokens. Let
me cheat and log in here. Notice when we log in, it doesn't actually show there.
That's a little, it's a little thing I could improve on my system. When we log in, we
grab the user, but we have no way of grabbing the API tokens. We're not part of our
api.

Oh, hold on a second.

So over here I'll do a force refresh. See, we're not logged in right here. So it
doesn't show any API tokens. If we log in with our user, you can see it works. It
says refresh to see tokens. We can't see the tokens on page load. Uh, when we log in,
it's cuz they're not put over api. But if we refresh, there we go. We have two tokens
for this user, which we can use. So next, let's write a systems that we can read
these tokens and authenticate the user instead of using our session authentication.

