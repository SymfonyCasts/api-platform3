# Validation Groups

Coming soon...

Okay, so now that the password property is of legitimate part of our API via the
plain password field, let's add some validation because you can't create a new user
without a password. So a certain slash not blank

That

Actually is going to cause a problem, which we're gonna discover in a second. But
let's blindly move forward and pretend that everything is fine. Copy our first test
cause I'm gonna create a second test here to make sure that we can update users. So
in this case we'll call it test patch to update user. And this will be a bit simpler
here. I'll start by creating a user user equals user factory and create one and then
I'll say acting as user. We're gonna log in as that user cause we need to be logged
in in order to edit users. And then we will patch to slash api slash users slash and
we will edit our own user record down here. For the j s Jsun, I'm just gonna send an
email. I mean username will say changed and we'll assert that the status is 200 and I
don't, I don't need any of these other spots.

All right, that looks good. If, just to remind you on our user endpoint up on patch,
here we go. We're just requiring that the roll, we have roll user edits because we're
logging in as a full user, we should have that. Everything should work just fine. So
let's run Symphony PHB bin slash PHB unit. Dash dash filter equals the name of our
method and oh 200 expected to get four 15. That's a new one for us. So I'm gonna
click to open the logs here and then I'll view source to make this a little more
clear, we get four 15. The error we get is the content type application slash j s O
is not supported. Supported mime types are application slash merge patch json. Okay,
let's unpack this a little bit. We are sending a patch request and we understand that
patch is quite simple, right? You can send a subset of the fields and only those
fields will be updated. Well it turns out the topic of patch requests is actually
quite complex and there are actually different competing formats of data that you can
send to a patch request that mean different things. Currently an API platform only
one format is supported in its application slash merge patch jsun application slash
merge patch jsun is for the most part what you'd think it means.

That format basically says if you send a single field, then only that single field
will be changed, but also has like other rules how you could send email set to null
and that should actually remove the email field. That doesn't really apply here. But
the point is, this defines the rules about what it means to kind of patch or update
from a partial set of json. And if you wanna know more about it, there's a little,
there's a document that describes exactly what it does. It's actually very short and
very readable and kind of cool. So the point is that there in theory, API platform
only supports one at the moment, only supports one way, one format for of jsun for
our patch request. But in theory in the future they might support more and the format
they support is this application merge patch j s O. And so when you send a patch
request, they require you to set the content type header to application slash merge
patch json so that you're explicitly telling API platform which t how you want it to
handle your J S O. So in other words, if we go down here and pass a header's key

With content type set to application slash merge json, if we try this now, it still
fails, but now it's failing with a validation error. So that fixes the content type
problem. So the takeaway is your patch method actually requires this content type
header. Now you might be wondering, wait a second, we did a bunch of patch requests
over here inside of the dragon treasure and we didn't need it then like what's going
on? That was kind of on accident inside of our Dragon Treasure class and our first
tutorial, let's see, here we go. We actually added a formats key and we did this so
that we could could add CSV support to our dragon treasure resource. Well it turns
out for kind of some complex internal reasons, by adding this format's key that
actually removed the requirement for needing that header. So we were kind of getting
away with what, without setting the header in our dragon treasure test, even though
we should be setting it. So since I was adding CSV format here, I maybe should have
added these formats just to the GIT collection endpoint because that's really the one
where we wanted that CSV export anyways. We really should need it everywhere. That's
why we didn't need it for Dragon Resource treasure. But we do need it for user
resource treasure.

Now if adding this header is really annoying every time you call patch, that's
another situation where you could add a custom method to browser and you could maybe
have a method called API patch, which looks the same but then adds that header
automatically for you.

All right, so that's the little important thing you need to know about Patch. Now
let's go back and see what happened to our test. Cause it's still not passive. We're
getting a 4 22 and if we open the air now, ah, it's our validation, the password.
This yield should not be blank. This was an accident inside of user. Our plane
password property is not persistent to the database. So this is always going to be
empty to start. When we're creating a user, we absolutely do want this field to be
required, but when we're editing a user, the plane password field's gonna be
required. But that doesn't mean we need the user to send us a password. They want to
change the password, they can, but if they don't need to change the password, they
don't need to send a plain password. So this is the first spot where we have
conditional validation that should happen on some operations but not on other
operations. So the way we fix this is with validation groups, which is very similar
to sea realization groups. So check this out, I'm gonna go up and find my
post-operation in here. We can pass a new thing called validation context with you
guessed it groups. And in here I'm gonna pass a group called default with a capital
D.

And then I'm gonna make up a new one called post validation. So when the validator
validates an object, it it by default validates everything that's in the default
group. And anytime you have a constraint by default that constraint is in the default
group. So what we're saying up here is we wanna validate everything that is in the
default group and everything that is in the post validation group and we can take
that post validation and go down to plain password. And here on that we can say
groups set to post validation. So now other endpoints like patch will not run this,
but the post endpoint will run this, this when you run it now. Got it. That test
passes and in fact all of our tests are passing. Now one quick note about this
validation Here in my user operation, I still do have put and patch, I haven't really
played with it much yet. But as I mentioned earlier, technically the new put does
support creating objects. So it's a little weird put, this could be used to create or
edit an object. So it's a little tricky. Do you need to run validation on the use on
the password? Do you not need to? So this might be another reason that you delete the
put operation

For simplicity so that you can have one operation for create and one operation for
edit. Alright, next we're gonna talk about making our serialization groups dynamic as
another way to be able to conditionally include or not include fields in your API
based on who, who's logged in.

