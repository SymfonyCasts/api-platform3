# User Resource

Coming soon...

We have our new user en entity, but it is not yet part of our api. How do we make it
part of our api? Ah, we already know it's super simple. Go above the class and add
API resource attribute. Refresh the docs. Look at that. Six fresh new endpoints for
our user entity. Thanks to our fixtures, we should be able to see data immediately.
Lemme try to get collection endpoint and look at that. It's alive. Nope. It is a
little weird that things like rolls and passwords show up inside of here. We'll worry
about that in a second. Quickly, I want to have say one quick thing about UU IDs. As
you can see, we're using auto increment IDs inside of our api, but you can totally
use a UU ID instead. And that's something that we'll talk about in a future tutorial.
Why would you use UU IDs? Well, sometimes it can make your life easier in JavaScript.
In JavaScript you, you can actually generate the UU i d in JavaScript and then send
it to your api. Sometimes that makes life easier in JavaScript because you know what
the ID of that resource is immediately, instead of needing to make the age X request,
wait for it to finish and then get the new auto increment ID back anyways. API
platform does supply, does support UU UIDs. There's a way for you to add a new U I UU
ID column here and tell API platform that that is your identifier.

If you do that one word of warning in some database engines, UU IDs are not a great
primary key for performance. So you might still want to keep the id but then have a
second UU i d, which is actually what you use an APAP platform, but that depends on
what your database engine is. Anyways, back to our user entity, it's returning way
too many fields and we know that problem. We know how to fix that problem up on API
resource, we're gonna add a normalization context key with groups. Set two user colon
read to follow that same pattern that we used over in Dragon Treasure and then DN
normalization contact set two user colon. Right now we can just decorate the fields
that we actually want to return. So we don't need to return ID because we always have
the at id, which is more useful anyways, but we do wanna return the email, so I'll
add a group's attribute on there, hit tab and get that use statement pass and right,
and we'll have user calling read. And this is also write also user call and Wright.

Now copy that. Let's go down here. Password, we do need the password to be writeable
but not readable. So I'm just gonna use user colon right on this. Now this still
isn't quite correct. The password field is meant to be the hashed password and we
don't actually want our users to send us a hashed password. We want them to send us a
plain password and then we hash it. That's something we're gonna solve in a future
tutorial when we talk more about the user object. But this will be a good enough
start for right now. Uh, above username. Let's also add user read and user write on
that. Cool, so let's refresh and beautiful and try our end points and beautiful email
and username come back. And if we were to create a new user, we do passing email,
username, and password. All right, so what else are we missing? How about validation
constraints? If we try out our endpoint right now empty, we get that nasty 500 air.
So let's fix that back over in a class. I'm actually gonna start above the class by
making sure that the email and the username are unique so we can pass the unique
entity

Above that pass fields and we'll do email first. And if you want to, you can pass a
message to this. Beautiful. And then let's repeat that same thing for the username
field. So update the field and also update the message. All right, then down here for
email, we're gonna want that as not blank. Let me put the assert in front of that and
I'll just tweak the you statement like last time. Nice. And then one more, we can
also PA may pass this, the email constraints, so the valid email address. And then
the only thing that we need right now is just above username. We'll add anot blank.
I'm not too worried about the password right now because that's a little weird.
Anyways. All right, so now if we try things, let me actually, let's pass just
password and beautiful 4 22 status code and we see the validation errors. All right,
so let's try a valid thing. Set an email address the username and the username. No, I
don't think this guy's actually a dragon. I think he, I think he might be somebody
else after the dragon's treasure hit execute. Got it. 2 0 1 status code. You see the
email and the username returned. So this is great.

We've got our new user resource in the api. It's got these six operations. We've got
validation Page Nation, if we wanted to, we can add filtering to it. We are crushing
it. Now we get to the really interesting part. We need to relate our two resources so
that each treasure is owned by a user. What does that look like in a API platform?
It's super interesting and it's next.

