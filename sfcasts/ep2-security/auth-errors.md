# Auth Errors

Coming soon...

When we log in with an invalid email and password, it looks like the Jason login
system is catching it and it's setting us back some nice json with Eric and Eric Key
set to inva credentials. If we wanted to customize this, we could, we could create a
class that implements authentication failure handler interface and then set its
service ID onto the failure handler key under Jason login. But this is good enough,
so let's use it over in our login form dot view app. I'm not gonna go into the
details of view, but I already have some state set up called error and if we set
that, that's gonna show up on the on the form. So basically we make the response here
using fetch and that the response is not okay. We're already decoding that jsun and
we're logging that. So we can do now, let's say error value equals data dot error
three to air key off the response. Now make sure that you have wet pack Encore
running in the background so that this recom compiles over here. I'll refresh,
actually you can click this little link here to put in a valid user, but then almost
type in a ridiculous email and perfect. We get inva credentials on top and we get
some red boxes

So the air case is working great. Now there is one kind of gotcha with the jsun
underscore login security mechanism. It requires you to send a content type of
application slash jsun, which we are doing inside of our ax call and you should do
this, but if our, if somebody forgets, we wanna make sure that it doesn't explode. So
let's see what happens if we comment out that content type and then go over here,
refresh the page, I'll type a ridiculous password in and it clears the form. If you
look down at the network here, it actually returned with a 200 status code and it's
just returning a user key set to null. And that makes sense in our security
controller, we're if there's, if the user's not logged in, we're returning user null.
So this looks successful to our forum and that's not what we want. If for some reason
the jsun underscore login mechanism is skipped, but they post to this endpoint, we
wanna return a 4 0 1 status code that says, Hey, you need to log in. So let's do
that. We can say, if not user sir, then return this arrow json. It doesn't really
matter what this looks like. You can get fancy here

And I'll just put a message there, message key and error key with a message and I'll
even fix my typo. Cool. Now the key thing here is that for a second argument we need
to pass 4 0 1 for the status code. And down here we can simplify just a little bit
cuz now we know that there will be a user. Beautiful. Now over here if I just submit
another bad password, beautiful works, we get back that 4 0 1 and we can see our
error up there. So that's awesome. So let's go back and do log in for my view and
we'll put our content type back. All right, cool. So next, let's log in successfully
and figure out what we want to do when that happens. We're also gonna talk about this
session and how we authenticate our API requests.

