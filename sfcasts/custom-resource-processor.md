# Custom Resource Processor

Coming soon...

Right now, because we have not specified the operations key on our API resource, we
get every possible operation on our quest. But really, we just need a few of them. So
I'm going to specify the operation, which I usually do on my entities, or my
resources, operations. And the ones we want are new Git collection, new Git, so we
can fetch just a single one, and new patch so that you can update so that users can
update the status of an existing quest when they complete it. So now when we refresh,
perfect, we have just the three we want. All right, so let's turn to talk about the
state processor. That's what's going to be called when we make a patch request. And
as I mentioned, the idea is that users can make a patch request to change the status
of a quest when they've completed it. So to play with this, let's create a new test.
So down here in the test functional directory, we'll create a new test class called
daily quest resource test. Make this extend an API test case that we created last
time. And then we're going to use the reset database from foundry to make sure our
database is empty at the start of every test and use factories. The use reset
database we don't technically need because we're not talking to the database, though.
It doesn't hurt anything. And if we start talking to database later, then we'll have
it. All right down here, let's say public function test patch can update status. So
first thing is I'm going to create a new date time object that represents yesterday,
minus one day. Because remember, in our provider, we are creating daily quests for
basically today, through the last 50 days. So since we're making a pet patch request,
our provider is going to be called first to load that. And then the new JSON is going
to be added on to that. So we need to use a make a request to a date that is going to
match one of our quests in our provider. So let's do that. This is our browser. And
then we'll say patch and the URL is going to be slash API slash requests, slash. And
then we'll say yesterday, format, y dash m dash d. Perfect. And then I'll pass a
second argument, which is gonna be the options for this. And we want to first we're
gonna pass the JSON we're gonna be passing. So let's say status completed. status
completed. This comes from the enum. So we have an enum for status, but behind the
scenes is either active or completed. So we're passing active. We're passing
completed, I mean, down here, I'm gonna say assert status. 200. Dump. That'll be
handy in a second. And then assert JSON matches. That status is actually updated to
completed. Perfect. Now in reality, we're not actually going to save this updated
status anywhere. But we should at least see that the final JSON has status completed.
So let's copy this test name. And I've run over say symphony php bin slash php unit
dash dash filter equals that name. And 415 I forgot something. So the 415 is the
content type application JSON is not supported. I forgot for my patch requests. We
also need to pass a header kind of annoying, but this is going to be a content type
header set to application slash merge patch plus JSON. This tells the system how what
type of patch we have. This is the only one that's supported right now. We talked
about this in the last tutorial that just needs to be back there for patch requests.
And now it passes. But in reality, all that's happening is that API platform is
loading this daily quest from our state provider. DC realizing this JSON onto it, and
then re serializing that object into JSON, there's no state processor at all
happening behind the scenes. But actually, I'm gonna comment out that status, I want
to make sure we actually are working with by coming out the status, oh, it actually
says it still works. Let me actually go minus two days and actually change this to
just day. Remember, in our provider, we kind of randomly make things active or
inactive. I think I selected one that is completed by default. There we go back two
days, we found one that was in an active state by default. And if we set status to
completed, there we go. Okay, so we can see it. So again, there's no state provider
processor happening, but it does load our daily quest, it does DC realize the JSON
onto it. So it updates our status property of our daily quest. And then it re
serializes it at the bottom, we do want to do something when this when this happens,
so we're going to create a state processor. And we know how to do this, bin console,
make state processor. And we'll call it daily quest, state processor, another
brilliantly creative name. Perfect. Here it is, it's empty. And then of course, last
thing we need to do is we need to hook it up. So we want this to happen for the patch
request. So here, we'll say processor, we'll say daily quest stats, processor, colon
colon class. And just to prove this is now being hit in here, we can DD the data. All
right, let's try the test again. And got it. And you can see that the status is set
to complete it. So the order of things is that, because it's a patch request, it hits
our date, our state provider to load the daily quest matching that the serializer
updates the object with this JSON, and then it calls our state processor. By the way,
we put the state we put the processor on the patch operation. But we can also put
this down here. On the API resource class. In this situation, that makes no
difference at all. This is the only operation we have that even uses a processor,
there's no processor called for a get or get collection. If we did have, for example,
a delete operation or some other operations, then having the processor down in the
bottom would mean this is the processor also used for that operation. So in that
case, you might need to do something different in your processor based on the
operation, which you can do because we are past the operation as an argument. We're
actually going to see that later where we create a processor that handles deletes and
database saves when we go back to a custom resource for our entity. Anyways, this
instead of our state processor is normally where we'd save this data somewhere or do
something with it, we don't really have a database. But let's at least add a last
updated property to our daily stat daily quest objects, we can see the difference. So
what I mean is we're gonna create a new bump public date time interface last updated
property here. So it's gonna be a new property that's on our API. And then I'm gonna
go and make sure that it's populated inside of our state provider, so that it's there
when we fetch data. So how about quest arrow last updated equals new, I'll make a
date time immutable. And I'll put some randomness here. So minus percent d days, and
we'll have that be something random between 10 and 100. Cool. And now more
importantly, in our state processor, so we can see the difference. We're going to set
that to right now inside of here, and we'll see it in our response. So the first
thing we know that this daily quest state processor is only being used for daily
quests. So this data will be a daily quest object. To help my editor with that, I'm
going to say data is an instance of daily quest, daily quest. And down here, data
arrow last updated equals new date time immutable. Now. Alright, so watch this, when
we run the test, we're not doing an assertion for that. But we are still dumping the
output. And we can see I can promise you that is I'm looking at my watch right now.
That is right now in my world. So it did hit our state processor. Perfect. So now
let's go back to the test. Now I've got this working, I'm going to remove that dump.
Alright, next, let's make our resource more interesting by adding a relation to
another API resource a relation to dragon treasure.
