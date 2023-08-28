# Entity State Processor

Coming soon...

All right, so we've done the provider side of things for our new user API API
resource. Now let's turn to the processor actually saving things. So we do have some
nice tests for our user endpoints summed up on user resource test. We have one test
post to create user, it posts some data, it creates the user, then it actually tests
to make sure that the password we posted works by trying to use it to log in. So I'm
going to add a little dump in here to help us see what's going on. I'm going to copy
that method name. And let's run that symphony php, then slash phpunit, dash dash
filter equals test post to create user. And it fails current status code 400 expected
201. And the dump here is really helpful. You can see we get the error that we saw
earlier unable to generate an IRI for the item of type user API. We already talked
about what's happening. The JSON is sent, and it is successfully DC realized into a
user API object, yay. Then the core doctrine persist processor is called. Because
when we use state options, API platform automatically sets our provider and processor
to the core doctrine stuff, unless we override it like we've done for provider. We
haven't done that for processor. So it uses the core persist processor. That's good,
except that our user API isn't an entity. So the persist processor does nothing.
Finally, API platform tries to serialize our user API back into JSON to return it.
But our user API has no ID populated. And so it fails to generate the IRI watch, I'll
actually prove it in user API. Let's temporarily default ID to five. When we try to
test now, it actually appears to work. So while it failed, but it's actually getting
further. It's failing down here on user resource test line 33. So it actually
successfully gets through this part. It is a status 201. And then it just fails to
log in because we're still not actually saving that user. But if you look at the
response on top, yeah, it successfully returns this user. But again, it's returning
this, but nothing is actually saving it. It's not in the database. So let's change
this back to null. And the way that we're going to fix this is, of course, by
creating a state processor for this. So let's spin over and we can use bin console,
make state processor. And I'm going to call this entity class DTO state processor,
because again, we're going to make a state processor that's going to work for any of
these situations, where we have a API resource class that's kind of tied to a
doctrine entity. We're gonna use this later on our dragon treasure. So there's our
new processor. And let's immediately hook this up inside of here. Processor entity to
DTO entity class DTO state processor. Perfect. All right. So this now means that
whenever we are posting, patching or deleting something, our processor is going to be
called. And what is data, I bet you can guess, but let's dump it and rerun the test.
And it is our user API object. The JSON we sent is DC realized into a user API
object, and then that user API object is passed to our state processor. The user API
object is our central object inside of API platform for this request. Alright, so our
job in here, our main job in here is we need to convert this user API object. I'll
actually say, assert data as an instance of user API. We need to convert this to a
user entity so that we can save the user entity. I'm going to do that by saying
entity equals and I'll make a new helper function here called map DTO to entity and
we'll pass in our entity or I mean our user API object and then we will dump that
down here. Let's create that private function map DTO to entity. This is going to
take in an object DTO and return another object. Now again, we know this is actually
going to take in a user API and it's going to return a user entity object, but I'm
going to try to write this class to be generic to all classes so that we can use it
later, though we are going to have some user specific code down here temporarily. In
fact, to help matter right now, I'm going to add another assert that this DTO is an
instance of user API just to make life easier. Now, we need to really think of two
different cases for this processor. The first case is that we are posting a brand new
user. So in that case, this DTO is not going to have an ID, the ID on this is going
to be null. And that means we probably want to want to create a new fresh user
object. But the other case is if we were making, for example, a patch request to some
specific user. In that case, the item provider will first load that user from the
database. Our provider will turn that into a user API object with the ID equal to
six. So in this situation, when we get this user API DTO, its ID is going to be equal
to six. And if you think about in that case, we don't want to create a new user
object, do we? We actually want to query the database for the user with that ID. So
we need to have both the cases where we have a new user here, or we're querying for
an existing user. So let me undo my changes to the test. I don't break anything. So
we're gonna do is we're gonna say if DTO arrow ID, so if we have an ID, this is the
case where we need to query for that user. And to do that, we're gonna need the user
repository. So let's add a constructor up here. Private, private user repository,
user repository. And then down here, it will be entity equals this arrow user
repository arrow find DTO arrow ID. And then I'm actually going to put code that says
if we don't find an entity with that ID, I'm going to throw a new exception, not an
exception. This is going to be a normal exception is actually going to trigger a 500
error if this happens. So I'll say entity, percent D not found, and then we'll pass
DTO arrow ID. Now, you might be wondering, shouldn't this be some sort of 404 like
through a not found HTTP exception here? The answer in my case is no. If we have an
ID here, it means that we have already we have a situation like this, like a patch
request. And it means our state providers already successfully queried for that
object and found it. So there should be no way for us to get to a point here where we
have a user API object with an ID, and that ID is not found in the database. Now
there are some exceptions to this if you for some reason, allowed your user to change
the ID, then you would need to do this. Or if you allowed users to create brand new
objects via a put request. But for most situations, and for my situation, this is if
this happens, something went wrong. So I kind of want to explode. Alright, so if we
have an ID, we've queried for the ID. And if we don't have an ID, this is we're going
to say entity equals new user. And that's it. Then down here, regardless of the case,
now we're going to map the DTO object to the to the entity object. This way the DTO
may have been this is gonna be pretty boring code here, I'll do it really quickly.
And for the password, I'm actually gonna put it to do there because that needs to be
the hashed password. We'll worry about that in a second. I'm actually also going to
put it to do for handle dragon treasures. So for now, I'm just going to even worry
about the email and the username. And transfer those on and at the bottom or return
entity. Alright, so we've done things correctly, we're going to get the user API that
has this data on it, we're going to transform it into an entity, dump that, let's see
if it works, rerun our tests. And well, for a four, let's see what happened here. Oh,
of course, because I'd never actually put my test back together. There we go post to
slash API slash users. Now try the test again. Got it. There is our user object with
the email and the username transferred correctly. So next, let's actually save this
by leveraging the core doctrine processor. Then we're also going to make this
processor work when we're deleting users and finish our password by hashing it in the
end, we're going to get that test to fully pass.
