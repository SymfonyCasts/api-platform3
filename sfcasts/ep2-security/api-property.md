# Api Property

Coming soon...

We control which fields are readable and writeable inside of our code via the
serialization groups. What if you have field that should be included in the API only
for certain users? It's not something we can do out of the box with groups. For
example, find the is published field and let's expose this to the groups. Treasure,
read and treasure right to make that part of our API now for spin over and try the
tests. This makes one test fail our get collection of treasures because it sees that
there is now a new is published field being returned that wasn't there a second ago.
So the idea is that we only want this field to be returned for admin users or owners
of the dragon treasure. How can we do this? Well say hello to a new attribute that
you can use above your properties called API property. There is actually a bunch of
things that you can do with this, including a description that helps with your
documentation and also a whole bunch of more advanced things inside of here. But
there's even one called readable. So you could say readable, false. This is kind of
the serialization groups are sort of making this part of our API and then we're
saying, but it's not readable. So then if you try the tests, I'll actually do

That actually does make the test pass. It hides that field though it's not what we
want. One of the super cool options inside of API property is security. So for
example, we can set this too is underscore granted roll admin. And this is really
simple. When this object is serialized, if this expression fails, then is published
is going to be removed from the end result. So now when redo it, now I'm gonna run
the tests. They still pass meaning that is published field is not being returned from
our normal test. All right, so let me open up my dragon treasure resource test again
and lemme show you here. So this is the original test test get clarkstone treasures.
And when we're just anonymous you can see that it's not returning is published. This
test is passing. Now scroll down to test admin can patch to edit resource. And what
we can do here is when we create the treasure factory, let's actually control this
and make sure that it always comes with is published false. And then down here I'll
assert that the jsun matches is published false. So make sure that in this situation
we do have that field. So I'll copy that test name run over and use dash dash filter
to just run that test and that passes. So it is being returned when we are an admin
user. So one last thing I want to test here, which is for the owner. So I'm going to
duplicate that test.

I'll say test owner can see is published field and then we'll change a couple things
here. We'll I'm gonna rename admin to user for clarity and then we can actually
simplify this to create one and then when we create Thera Dragon factory Dragon
Treasure, we'll make sure that owner is set to our new user. So cool. So we're, and I
could change this to a get request, but this is fine. We're gonna show it as a patch
request and when it's serialized we're gonna make sure that it is published field
comes back and we don't expect this to pass yet cause we haven't done anything for
this and it, so let's copy that method name run just that test and yeah, it does
fail. So we know the drill here over here on this security thing. We could in line it
like we did before. We could say uh, or object dot owner, get owner like that. But
this is the whole point we created the voter, we don't need to do that. We can say is
granted edit and then we can pass the object. We still have access to that nice
object variable inside of these property securities.

So we try to test now. Got it. Notice also, I haven't used it very many times, but
there's also a security post de-normalized. And what's interesting, just like with
the of the security post, you normalize on our operations, this is run after the new
data is serialized onto the object. What's interesting about it here is if the this
expression returns false, the scent data would actually be reverted. So for example,
if the user, if the treasure started as is published false and then the user changed
it to true and then security post norm de normalize returned false, it would actually
reverse that is published field back it up and change it back to its original value.
The one thing about security post you normalize that doesn't happen on get requests,
it only happens when data is being de serialized. So next, let's finally fix our user
endpoints so that they hash the password before they store it in the database. This
will involve a new topic called Data State Processors.

