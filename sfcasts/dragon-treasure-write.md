# Dragon Treasure Write

Coming soon...

Let's get our write endpoints working for our new Dragon Treasure. So let's see here.
There is a test called test post to create treasure. That sounds like a good one.
Symfony PHP bin slash PHP init dash dash filter equals test post to create treasure.
And that explodes actually ran a couple of tests. We can look at any of them. So I'm
going to say the same thing. No mapper found for Dragon Treasure API to Dragon
Treasure. So following the logic here, we know what happens internally. When we try
to create something, it deserializes the JSON into a Dragon Treasure API object, and
then calls our processor. Our processor takes that API object and tries to use the
micro mapper to map it over to our entity. So we're missing is that Dragon Treasure
API to Dragon Treasure mapper. There's no problem. Let's create that. In source
mapper, I'll create a new Dragon Treasure API to entity mapper. Y'all know the drill
at this point, it's going to implement mapper interface. Use the as mapper to say
that this is going to come from a Dragon Treasure API to a Dragon Treasure. Then
we'll implement the methods. This is going to be very similar to our user API to
entity method. So one of the things we need to do in load is if if there is an ID,
we're actually going to query for that object. So I'm going to go ahead and add a
constructor right now. The private Dragon Treasure repository repository. Down here,
I'll use my normal DTO equals from an assert that DTO is an instance of Dragon
Treasure API, just to keep my editor and I both sane. If the entity is going to be
actually I'm going to steal some code from our other mapper, it's gonna be so
similar. So I'm gonna copy the code there, paste it here, it cancels, I don't
actually want that you statement. So let's rename this to just entity. So if the
detail has an ID, it means we're editing it and we want to find it. Else, we're going
to create a new Dragon Treasure. And it shouldn't happen, we'll have the exception in
case that's not found. Now, one interesting thing about our Dragon Treasure entity is
that it has a constructor argument, which is the name. And there actually is no set
name method on it. So the only way to set the name is through the constructor. So
it's actually a case where I'm going to transfer the name from the DTO onto the
entity right there when I instantiate it if it's a new one. Alright, then down in
populate, I'll start with the same code that I have on load. Also add entity equals
two. And one more assert for entity being an instance of Dragon Treasure. And then
I'll put to do for the other fields down there, let's just at least get this thing
mapping. Alright, so when we ran our test earlier, it actually ran three tests that
match that name, let me make the test name a little more unique. So this is test post
create treasure using the normal login mechanism. So we're going to say with login.
And let's rerun that treasure, rerun just that test. And okay, current status code
500. Let's see what's going on. Okay, good. We got further, it's now exploding when
it hits the database. So it is trying to save and it's explaining, it's complaining
because owner ID is no. Now as a reminder, owner is supposed to be optional. If you
pass no owner, we set it to the currently authenticated user. So we need to re add
that logic. And we'll do that in a second. But this failure is actually coming from
earlier, it's coming from line 71, which is right here. So you see, the first thing
this test does is test our validation, it submits no JSON, and make sure that our
validation constraints are hit. And we don't have any valid validation constraints.
So instead of failing validation, it's actually trying to save the database. So let's
re add our validation constraints. And again, this is just like normal, except we put
it on our API class. So we want to have our name, not blank, our description, not
blank. And we'll value greater than or equal to zero and cool factor greater than or
equal to zero and also less than or equal to 10. And that should do it. Alright, so
let's run the test again. It's probably gonna hit that same error. Yep, 500 error.
But look, now it's coming down from line 78. So it means that we are hitting our
status code here. And then down here, it's posting a valid response, trying to save
the database, but it can't because like we saw a second ago, the owner ID is still
no. So this is one of the great things about the having these mapper objects. In
dragon treasure API to entity mapper. Normally, we're going to do things like entity
arrow set value from arrow value. DTO arrow value, we're just gonna be transferring
data from one to the other. But we can also just set custom things here do any weird
transformations we want. So check this out. We're gonna say if the DTO has an owner
property, then we're going to set that on the entity. And actually, I'm not going to
set it onto the entity yet. I'm just gonna dump that. This would be the case where
you are sending you are having a test where maybe you are choosing to actually send
the owner as something I want to talk more about that in detail. We're not doing that
yet. As soon as we try doing that, we're going to hit this dump here. We right now
we're going to fall into this L situation where we don't have an owner in the DTO. So
we can just set it to the currently authenticated user. So up here, like we've done
so many places before, we'll just inject the security service. Down here, we'll set
the owner to this error security arrow get user. Beautiful. Now we are still missing
the other field setting here. So if we try to run the test, we're still going to get
a 500 error. But if you check out the error now, it's failing because description is
null. So the owner is being set. It's now failing because description isn't on the
entity. Because we need to finish our work here. This is the easy stuff, right?
Entity set description, DTO arrow description, entity arrow set cool factor, DTO
arrow cool factor, just transferring from one to another. Very boring, but also very
explicit, very clear. I'll put to do down here for published. We're going to talk
about published in a minute. We are not setting the published field yet. All right,
run that test now. And it passes. Woo. But if we run all of our tests from Dragon
Treasure, we actually do have several failures. Let's talk about what these are next.
They're related to headers, some missing headers, security validation. And let's
tighten up our Dragon Treasure the rest of the way.
