# Simper State Processor

Coming soon...

Let's talk about how we publish a Dragon Treasure. First of all, publishing is easy.
You can just make a patch request to the treasure's endpoint and set isPublished to
true. Done! But what if when a Dragon Treasure is published we need to run some
custom code? Like maybe we trigger some notifications on the site that a new treasure
has been published. Well one option to do this is to actually create a custom
operation for this like maybe post slash API slash treasure slash five slash publish
and you can do that and I don't have a big problem with it. It's not super restful
though that shouldn't always stop you but it's actually not really necessary. We can
keep that simple patch request and still run that code that we want. How? By using a
state processor but then detecting that the isPublished change from false to true. As
you can see we do already have an isPublished field on Dragon Treasure and so let's
start by creating a test that does exactly what I just said that does the patch to
change that. So at the bottom I'm actually going to copy this last test here let's
paste that and then I'll rename it testPublishedTreasure and it's gonna start
basically the same way. We'll have a user, we'll be the owner of that user. Then
we're gonna start with isPublished false and then down here we will log in as that
user. We'll make a patch request to slash API slash treasure slash that ID and then
we'll set isPublished to true. This should be a 200 status code and then we're just
going to say assertJSON matches that isPublished is true. Simple enough. Alright
let's copy that test name, spin over, symphony.php, bin slash phpunit, dash dash
filter equals. Bam! And it fails. It fails in that last line. Expected false to be
the same as true from line 236. So it is actually failing right here. It's coming
back as still isPublished false. Why? Well this actually comes from an earlier
tutorial. If you look at the groups above this the admin user can write it but
there's no owner colon read or any treasure colon read. Owner colon write or treasure
colon write. So we wanted to make this always writable. So to make this writable by
the owner, to make this writable by anyone, we're actually gonna write treasure colon
write. That means anybody that has access to modify this treasure can write to this
field. And in reality if we look up here on our patch security we already have
security that only allows you to edit this treasure if you pass this security. This
comes from a custom voter who made the previous tutorial and basically it only allows
owners or admin to edit this treasure. So if you are an owner or an admin then one of
the fields that you can now modify is isPublished because we put it in the standard
treasure write group. So now this test should pass and it does. Awesome. So we need a
state processor so we can run our custom code. We actually already have a state
processor for Dragon Treasure which we originally created to automatically set the
owner to the currently authenticated user if it wasn't already set. So the question
is should we jam that same code inside of here? Should we create a second processor?
And of course it's up to you but personally I like to have just one processor per
resource class. Just makes life simpler for me. So yeah you could have multiple and
then you could use decoration to have those called but I just recommend having just
one processor per class. So I'm actually going to rename this or I click on here and
go to refactor rename and let's just call it Dragon Treasure State Processor just to
be a little more consistent. Now there are two ways I mentioned in the earlier
tutorial when you have a custom state processor or a custom data provider there's two
ways to hook into the system. The way we did it a moment ago with the state provider
was to create a normal boring service and then to you kind of specify exactly which
core providers we wanted to pass in. The other way to do it the way we did in the
previous tutorial is actually to decorate the core processors. In this case we
actually decorated the persist processor from Doctrine which meant that whenever any
API resources was being saved since they all use the persist processor they would all
call this process method. So that meant that basically to hook this up all we needed
to do is just have this line and suddenly our process method is being called whenever
any entity was being saved in API platform and that's why we have this extra code
here to make sure that what we're saving is a dragon treasure because this will also
be called when a user is saving. So both ways are fine for consistency with the
provider I just created. I'm gonna refactor this to use the other way of doing things
and if you don't really understand the difference this will kind of help. So I'm
gonna get rid of as decorator. So as soon as I do this this is just a normal boring
service that at this moment nobody is using. Then because we're no longer decorating
this anymore we're not gonna automatically pass the core service here for
interprocessor. So I'm gonna break this onto multiple lines. There we go. And then to
have it pass us the interprocessor we'll use that same trick we did in the other in
the provider and say autowire service and then we can use that same service ID that I
had earlier the persist processor but you can also just say persist processor colon
colon class and make sure you get the one from doctrine ORM. Oh there's not. There's
only one and I'll get rid of the as decorator use statement. So the result is nearly
the same. When with the decorator system we were being passed the persist processor
as the first argument and then we were basically decorating it. The difference is
that with decoration our service kind of became the core persist processor which
meant it was instantly used everywhere in the system. Now this is just a normal
service and nothing is using it yet. So to get it to be used it will go into our
dragon treasure and we want this to be used for both our post and our patch
operations. Let me go up here and we'll say processor equals dragon treasure state
processor colon colon class and they'll repeat that down here for patch. So now
whenever our dragon treasure saves it will call our state processor and we've
injected the core processor into it so we can set that down here. It's kind of a
really simple setup. So now I'm actually gonna run all of the tests to make sure we
didn't break anything and beautiful. So we that means we do have everything set up
correctly. If we had for example not set this processor so that it wasn't being used
that actually would have made some of our tests fail because we would have missing
that logic that automatically sets the owner. So let me put those back so that proves
that our class is working. So the nice thing about doing the processor with kind of
this way without decoration is that you don't need this conditional code anymore.
This will always be a dragon treasure. In fact to help my editor and kind of prove it
we can start with assist assert data as instance of dragon treasure. Yep you can see
my editor is already telling me hey this code down here isn't needed anymore dude.
I'll get rid of this code down here. It's just a little bit simpler. And the tests
still pass. Great so we have really done nothing more than refactored our processor
to the different the new way of doing it. So next question is how inside of this
processor can we detect if we just went from is published false to is published true.
Let's finish this up next.
