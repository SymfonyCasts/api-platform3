# Json Login

Coming soon...

So on this homepage, which is built in view, we have a login form. The idea is when
we submit this, it will send an age x request with an email password. This is built
over here in assets view login form dot view down here and near the bottom. Here we
go. You can see we're making a post request to slash login and we're gonna send the
email and password as jsun. So we need a security endpoint where we can send this,
they'll read this and log in our user. Fortunately, symphony is a built-in mechanism
just for this to start, even though this controller won't do much, we need a new
controller. So in the source controller directory, create a new pH class, I'll call
it security controller. And this will look very traditional, more extend abstract
controller. Okay, Paul function log in. That would return response, the one from H T
D P Foundation. And the really important thing is actually is actually this route
above. So I'll do a route annotation tabu statement. We'll make this slash login to
match what our JavaScript is already going to be sending to and we'll give it a name.
How about app on your score? Login.

And we don't need to do this. We can also say methods post endpoint asara is really
only meant to match post requests. So as you'll see, we're not gonna process the
email and password in this controller, but this controller will be executed after a
successful login. So what should we return after I successful login? I don't know.
And honestly it just depends on what would be useful in our JavaScript. I haven't
thought about it much yet, but maybe the user id. Let's start there. So to get the
current user, I'm actually gonna use a cool new trick from Symphony where you use an
attribute called current user and then I can type in my normal user entity and say
User and then I'll say equals null in case the user is not logged in for some reason.
We'll talk about that in a little while and then we'll return this arrow json and
we'll pass. How about user set two? If we have a user, user arrow, get id Ls? No.
Cool. So this doesn't do anything yet, but it's ready. The really important thing
here is that we have a route because now over in config packages secure to that
yammel, we can go into our main firewall and we can add jsun underscore login and
then below that check path,

And we're gonna set it to the name of the route we just created. App underscore
login. This activates a security listener. Basically it's a bit of code that's gonna
be watching for us to send a post request to this url, which is slash login. It will
then automatically grab the email and password off. Uh, it will decode the jsun from
that request. Read the email and password off of it and log us in. No, we need to
tell it what keys in the jsun are email and password gonna be. As a reminder, we are
actually literally sending email and password keys. So below this set username path
to email and password, path to password done. But if we post an email and password to
this endpoint, how does the system even know how to query the database for that user?
Well, in episode one we ran the bin console, make user command that created a user
entity for us with the basic security stuff that we need. And it also Insecurity in
the AML created a user provider. This is an entity provider, which basically says if
we send the security system, when we send the security system an email address, it's
gonna not to query from the email property of user to get that. So this will be able
to read the email key off of there, query for the user, and then it will check the
password. So in other words, this is ready.

So if we look at log inform view, this is already set up. This handle submits gonna
be called and we submit the form, it's gonna make the Ajax call. So let's try this
thing. I'll go over and refresh just to be sure. And let's log in. We don't need, I'm
not worried about trying a real email password yet, so just try something fake and
okay, nothing happened. But open up your browser's inspector and go console. Yes,
look at this, you can see a 4 0 1 status code and it dumped this error invalid
credentials that's coming from right here after the response finishes. If the
response is not okay, meaning there is an error, we are decoding the J S O N and
logging it. So right now our endpoint is actually returning a little object with
invalid credentials. So next, let's turn this error into something we can see on the
form. Handle another error case, and then think about what to do on authentication
success.

