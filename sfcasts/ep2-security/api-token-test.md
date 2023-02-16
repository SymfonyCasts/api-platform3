# Api Token Test

Coming soon...

What would it look like to have a test like this where we log in with an A P I key?
Let's find out. Let's create a new method down here, public function test post to
create treasure with a p i. Key key. And this will start, you know, pretty much the
same ways before. In fact, I'll copy the start of the previous test, remove the
acting as, and yeah, we'll do that same thing. I'm also gonna add a dump right here.
So we will send invalid data and expect a 4 22 status code. So copy that method name.
Let's spin over and we'll run our test and filter for our new method. And no surprise
we get a 4 0 1 status code because we are not authenticated at all. So to start this,
let's send an authorization header, but like an invalid one. So as I mentioned
earlier, there is actually just a header's ki we can pass here and we'll pass an
authorization header set to the word bear. And then food. This should still fail, but
awesome. We're dumping out here. You can see it has a different merit error message
here. It says invalid token, invalid credentials. So our token system's working, it's
just that we don't have a real token yet. So now let's create one. So like usual,
we're gonna authenticate with an API token. We need to have one in the database. So
let's start with token equals

Api, token factory, colon, colon, create one. Now do we need to control any data on
this? We actually do. As a reminder, if we open up the Dragon treasure entity, our
post-operation, if we scroll up here, requires a role called role treasure create.
When we do normal authentication, thanks to our role hierarchy, when we do, when we
log in via the login form, we get roll full user, which means we get all these rolls
by default. But when we log in with an API key, if we wanna have roll treasure
create, we need to have that scope. So if I want this test to actually work and hit
the validation step, we need to control the scopes. So we'll override the scopes
property on our API token and we'll say API token calling, calling scope, treasure
create. All right, awesome. And then down here we'll pass token arrow. Get token to
pass the token string. All right, let's try this thing. We're gonna test and oh, I
actually have an error expected argument type array string given at property path
scopes. So you can see this actually coming from Zens Truck Foundry and makes sense
scopes here instead of API token. That's a property that holds an array. I'm passing
in the string, it gets pretty mad about that. So let's try this now with passing an
array of scopes and got it. We see the beautiful 4 22 validation errors.

All right, let's try one test with an API token that doesn't have this correct scope.
So let's copy this method. I'll paste below. And this case, we'll call it test post
to create treasure denied without scope. So in instead of this time will create a
token that has scope treasure edits, which should not allow us to create a treasure.
And down here we're going to trigger a 4 0 3. And this time let's actually run all of
our tests. So <inaudible>, PHP bin slash php units, and got it. Everything passes.
Um, but we do have a couple of dumps still.

You can see our 4 22 and then our 4 0 3 coming back. All right, so let's remove the
dumps from both of those spots. By the way, if you use API tokens a lot in your test
passing, this authorization bear header can get kind of annoying. So browser has a
way where you can add, where you can create a custom browser, where you add custom
methods. So one idea might be that after you create your browser instance, you're may
be able to say off with token, and then you pass it the array of scopes that you want
right here behind the scenes. This would create the APA token in the database, and
then actually set this authorization header as one of the default headers. This
totally does not work right now, but if you created a custom browser, you could get
that to work. Check out the browser documentation to see how to do that. All right,
next in API platform at 3.1. The behavior of the put operation is changing. Let's
talk about how it's changing and what we need to do in our code to prepare for it.

