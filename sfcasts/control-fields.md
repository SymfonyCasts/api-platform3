# Control Fields

Coming soon...

When your API resource is on an entity, serialization groups are a must because
there's definitely going to be certain properties that you want to show or not show.
But serialization groups add complexity and one of the biggest benefits of having a
separate class for your API is that since your API class is meant to represent your
API, you shouldn't need serialization groups because all these properties should
probably be part of your API. But that's not always exactly the case and we just ran
into the situation where we realized that password should be a write-only field. So
let's try to replicate some of the complexity that our user entity originally had in
our API by avoiding needing to use serialization groups. So first, in
UserResourceTest, down here, I'm going to remove the dump and after we assert 201,
I'm going to assert that the password property is not returned. So to do that, we can
actually say use passes a callback with a JSON argument. So this is a little syntax
from browser we can call use and there's a few different things you can type in here
and the library actually detects what you're type inting and passes you that object.
So one of the things you can type in is a helper JSON object. It'll take the JSON
that was returned from this response, kind of put it in this little JSON object for
you and then you can kind of play with it a little bit. So we'll say assert missing
password. If you try that, that fails because password does exist. Alright, so let's
play with some of the options for customizing our properties a little bit. When the
easiest ones and one of my favorite is to use API property. In this case, there is a
readable false. So we're on through writable, but not readable. And that fixes
things. How beautiful. Let's repeat that for ID. ID is really kind of a worthless
field since we have our IRI. So if I run that, that's going to pass ID is being
returned. Over here, I'll copy this. Actually, I'll copy just the readable false
part. And I'll say readable false. And while I'm here, I'll say identifier true. I
don't need to do that. It guesses it, but that makes me feel a little better. And now
that passes. Alright, so I want to move on and dive a little bit deeper into some of
our options, some of our other options. So I'm going to copy the next test name, test
patch to update user. And run that one, symphony php bin slash php unit. Patch dash
filter equals test patch to update user. And it passes immediately, which by the way,
is just amazing. So our patch is already working. I love that. Now to show a few
other options for hiding fields in certain cases. I'm going to add a new kind of fake
field here that we're going to update. So we're doing a patch request, and we're
going to pretend that we are trying to update a field called flame throwing distance.
Set to 999. And down here, I'm going to dump so we can see what the response looks
like. And I'm gonna do one other thing over in our state processor. Right after we
set the ID, I'm going to dump the data there as well. So that we can see, and let me
just run the test, it's going to help you understand kind of what I'm getting at
here. So right now when we run the test, you can see that we have a dump up here with
flame throwing distance 999, and it returns 999. So what I'm showing off here is the
fact that right now this field is readable and writable. It's just a normal field
down here. So it's readable and writable. And we're showing that off by saying that,
hey, we are allowed to pass 999. And that is actually what is ultimately passed to
our processor. And then when it's serialized, the field is also readable. So this is
kind of showing that it was writable. And this is showing that it is readable. So now
we have this kind of cool situation set up, let's start playing with a few things. So
in user API, I'm going to first start with that same API property, we'll say readable
false. So we've already seen this. And when we run the test, you can see up here that
the 999 was still deserialized on the user API. So it's still writable, but it
doesn't show up down here. So it's not readable. Cool. And now if we also pass
writable false, we'll see that up here in our state processor, it's just 10. We just
set this to our provider sets us to a random number between zero and 10. So it's not
writable, and it's still not readable. Awesome. So another way you can do this, and
in theory, they should be identical, so you can pick which one you want is you can
set normalization context up here. Now, this is something we set in the previous
tutorials on our entities. So we could set groups, but instead of groups here, I'm
going to say abstract normal, I'm going to set a key called abstract normalizer,
ignored attributes, and set that to an array. And here we can say flame throwing
distance. So it says when we're normalizing, so when we're going to JSON, I want to
ignore that property. So this should make it writable, but not readable. And when we
try it, exactly, it was writable, but it's not readable. And of course, we can do the
same thing with D normalization context. So if I copy that, put a D on the front of
it, it should now be not writable, nor readable. And yep, flames are in distance one,
it was not writable, and it's not readable. Again, these are just kind of different
options, they should all work the same, it's possibly you'll find some case where,
for some reason, one doesn't work quite the way you expect it. So you have these
other tools. So I'm going to delete those. And one other way you can do this, which
is kind of nice, is you can just ignore it completely. So down here above, we can use
a new attribute, an attribute called ignore. This comes from Symfony's serializer
system. And that makes it readable, not readable, nor writable, it's just completely
ignored. And we can see that over here, it was not written. It's not readable. Cool.
All right, let's reset all that dummy code. So I'm going to get rid of the ignore,
see if I have any extra use statements up here. Processor will get rid of that dump.
And in our test, we'll get rid of that extra field and that dump down here. Cool. Now
one more thing I want to kind of point out here is that right now, we can actually
change the ID in a patch request. So I'm just going to set 47, I just made that up.
And it fails with a 500 error. If I pop this open, it says entity 47 not found from
our state processor. So it's actually coming from down here. It's reading the ID up
here, it's trying to find that in the database, and it's not there. But if it if I
had picked a valid ID, it actually would have changed to that user entity would have
updated a different user entity. So that's a big no no, we do not want the ID to be
writable. So the full flow here is that our provider found the original user entity
with this ID, we map that over to a user API object, the ID was then changed on the
user API object to 47. And then we try to query for an entity with that ID. And
that's ultimately what we would have saved to the database. open user API to fix
this, I'm going to add a writable false, and you can also use the at the ignore
attribute that we saw a second ago, since we don't really want this readable or
writable. So the ID property ends up really being just there to be the IRI. But it's
not actually part of our API. And now if we run that test, it passes because it's
ignoring that new ID. So it's not trying to query for it. Life is good. All right,
while we're here, in user API, there's two other properties that at least for now we
want to make read only. So I'm going to above a dragon treasures, I'm going to make
this writable false. We're gonna talk about this later, maybe we can allow dragon
treasures to be created or to be set on a user. But for now, we're gonna say writable
false. And we'll do the same thing down here for flame during distance, because this
is really just a fake property that we're generating as a random number anyways. So
one other way to control whether a field is readable or writable, and we will see
this in a little bit in a second is the security attribute. So for example, if flame
throwing distance were maybe only readable and writable, if you had a certain role,
then you could actually use the security attribute to check for that role right here.
So that's another thing it's related to security, but it's one other handy way to
show or not show or allow a field to be written or not written. Now, finally, one
thing I'm just going to mention, we're not actually going to do it is that if your
input and your output for your class start to look really different, it is possible
you could have separate classes for your input and your output, you could have
something like a user API read and a separate user API, right. And so the user API
read, you would just use the for the operations that would just be the read
operations, like get and get collection. And for the user API, right, that's where
you would use the, the put patch and post operations. Now, I haven't actually done
this before, there's probably a couple of things you need to worry about. This might
be a case where with the user API, right, you actually need to set the output to user
API read. So that the user can send data and user API, right, but then you're
actually returning user API read coming back. Anyways, I don't want to go into
details, if that's not making sense to you, don't worry about it. But for those of
you that might have that case, I wanted to at least kind of put that out there. But
it's not something that I've personally experimented with yet, but it is an
interesting possibility. Alright, next up, let's polish our new API resource by re
adding validation and security.
