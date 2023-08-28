# Publish

Coming soon...

Let's run all those Dragon Treasure tests again. And we have three failures. One of
them is coming from Test Admin can patch to edit Treasure line 200. That is actually
over here. And the test isn't all that important, except for the failure. Assert JSON
matches isPublished in the response. Right now we don't have isPublished in our
Dragon Treasure API at all. Now this is a tricky field. Previously, that field was
readable only by admins or the owner, not by other users. So let's add that back. And
make it work exactly like that. So how about let's do a public bool isPublished
equals false. And then we're going to need to get into the state provider. I mean the
mapper. So down here we can get rid of this to do. Entity set isPublished to
DTOErrorPublished. So if we change published, we will sync that back to the entity.
And on the other side as well. It doesn't matter where, but we'll say
DTOErrorPublished isPublished equals entityErrorGetIsPublished. Cool, so there's no
security on that. It's just a normal field. And so when we run the tests, we are
going to have a lot more, we have a couple that pass, but the original test fails,
test getCollectionOfTreasures, because it's not expecting the isPublished to be
there. So let me show you up. This is the first test. And our thing at the bottom, we
say these are the exact properties that we should have if we are just fetching
treasures as an anonymous user. Since we're not the owner, we shouldn't see the
isPublished. So now we need to figure out how we can show this isPublished only if we
are the owner of the treasure, or if we're an admin. Now, a moment ago, we were
looking at Dragon Treasure API Voter. When we call this with the edit attribute,
that's actually exactly what it checks. It checks to see if we are the owner, and if
we are, it allows access. And it also checks to see, I mean, if we're an admin, it
also checks to see if we're the owner, and if it's true, we have access. So we kind
of want to include that field in the API if this voter passes. So we can actually
leverage security for this. So above this property, I'm going to add API property
with security, and we'll set isGranted. And we'll say, edit object. Now, if you
wanted to, you could change this attribute to something else, maybe like owner, if
it's more clear. Edit sounds a little funny here, since we're just deciding if we
should include this field in the response. But when we go over and run the tests,
this fixes that first test. So that passes. The isPublished field is no longer being
shown in that case. But curiously, we made another test fail, testPublishedTreasure.
This is coming from line 244. So let's pop over the test. I'll search for that. This
is coming from line 244. So let's pop over the test. I'll search for that. All right,
perfect. So as the name suggests, we're testing that we can publish this treasure. So
we create a treasure that is isPublishedFalse. We log in as its owner. And then we
send a nice patch request to set isPublished to true. And we assert that the JSON
matches down here. And this is actually the line that's failing. This took me a
little bit of debugging to figure out what was going on. What's actually happening in
this case is that when the JSON is deserialized, the isPublished is actually not
writable. So when the JSON is being deserialized, it actually calls our security
expression to see if it should be allowed to write this isPublished field. And that
is actually failing. This might be a bug. I have an issue open up on API platform
that's kind of talking about this. But even though we're making a patch request, so
there is an existing treasure, when this expression is called during deserialization,
object is always null. And since object is always null, it goes into our voter. Our
voter is only supporting it if object is a Dragon Treasure API. So this returns
false. No voters support this. And so therefore, when no voters support something,
access is denied. So it looks like isPublished should not be writable. So the
workaround here is a little weird. We're going to say, basically, allow access to
this field if object is null or isGrantedEditObject. So you have to kind of think
about this here. If we are reading a Dragon Treasure, then object is never going to
be null. There's always going to be an object. So the voter will always be called.
Object equals null is only going to happen when we're during deserialization, when
we're checking to see if we can write this field. So this actually effectively makes
this field always writable. Now, that's not actually a problem because we already
have security up here on post and patch that makes sure that we can write this field.
On post and patch, that makes sure that only in the case of patch, that only the
owner can edit this object. So if you're the owner, so once you've passed the patch
security, we already know that you can edit this object. So then it's okay down here
to allow you to edit the isPublished field. So another workaround, if you don't like
this, if this looks a little weird, another workaround that you could do is you could
have left the API security off entirely. And then to prevent the isPublished field
from being returned, unless you're an owner, you could actually have handled that in
the mapper. So here you could have put some security logic right here and basically
say, hey, only set the isPublished field on the DTO if you are the owner. Otherwise,
you could leave isPublished as null as a default. So it's always a good thing to
remember is that we do have full control of the data via our mapper objects as well.
I'm going to go back here, put that security expression back in.

Oh, and you know what? Let me go back to my mapper, I just actually realized I do want to keep that isPublished, just not in the if statement. All right, let's run over, rerun all the tests, and oh, so close, down to just one failure in testPublishedTreasure. This is actually our notification test, so over down here in the test, here is testPublishedTreasure. Earlier we were testing that when we do change from PublishedFalse to PublishedTrue, it actually causes a notification to be created in the database. Previously we did that via a custom state processor. Now we could do that in the mapper. When we're doing our drag and treasure to API mapper, we could check to see if the entity was isPublishedFalse, and if it's now changing to isPublishedTrue, we could create a notification right here. But this doesn't feel like the right place for me, because data mappers should be all about just mapping data. So instead, we actually are going to go back and create a custom state processor, which is a totally okay thing to do. So then console make state processor. Drag and treasure state processor. There it is. And now we're going to decorate this like ... Now we're just going to decorate our normal entity class to DTO state processor. There's a lot of construct method, private entity class DTO state processor, we can call that inner processor, and down here, we'll just return that. Return this here inner processor error process, passing it the arguments that it needs. Whoops, one more context. Oh, and you can see I'm highlighting red here. Once again, you don't have to do this, but it's not really a void method, so I'm going to remove that void. So now that we have this new processor, it's going to use our original one, we can just hook this up inside of here. So on drag and treasure API, instead of using the core processor, we're just going to use the drag and treasure state processor. The cool thing is it's still going to use our magic entity one internally, because we're injecting it. So at this point, we have changed nothing. Everything still works, except for that last failure. So down here, we can get down and do our notification code. Now you remember from earlier, the way we figure out if we're changing from is published false to is published true, is we can use the previous data that's inside of the context. So previous data. I'll head over and I'm just going to run just that test, dash dash filter equals test published treasure. Cool. So here we can see our previous data is the drag and treasure API with is published false. So this is the original one that we had inside of our test when we started. And then, let's also dump data, that will be even more interesting. So the original one has is published false, the new one has the JSON on it, it has is published true. And just like before, that's what we're going to key in on to send the notification. We've actually done this code before. So I'm going to, so I'm actually just going to copy in some code for this. I'll add a couple of use statements. So nothing really interesting. We just have the previous data, we're showing it is published, and then we're creating a notification down here. Now the kind of only really interesting part is that the notification is related to a drag and treasure entity. So we need to actually query for the entity using the repository, grabbing the ID off of the API. So we need to inject a couple of things here. First, the entity manager, so we can save private entity manager interface entity manager, and then private drag and treasure repository repository. There we go. That makes a little more sense now. So grabbing the ID off of the drag and treasure API, and then query for the entity. So we can relate that on the notification entity and save everything. And now it passes, and in fact, check this out, all of our drag and treasure tests pass. We have put everything back in. Amazing. All right, next up. Let's make it possible to write the drag and treasure drag and treasure property on user. This involves a trick and it's going to help us understand even deeper how API platform loads data.
