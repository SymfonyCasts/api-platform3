# The Serializer

The key behind how AAP platform turns our objects into J S O and also how it
transforms our JSON back into objects is Symfonys, serializer Symfony Serializer
is a standalone component that you can use outside of the API platform and it's
awesome. You give it any input like an object or something else and then it can
transform it into any format like J S O, XML or CSV to do that. As you can see in
this fancy diagram, it goes through two steps. First, it takes your thing like your
object and it normalizes that into an array and then it encodes it into the final
format. And the same thing happens in reverse. If we're starting with JSON, like
we're sending J S O to our api, it first decodes it to an array and then de
normalizes it back into an object.

Now in order to do this internally, there are many different normalizer objects that
work in different data. For example, there's a DATETIME normalizer that's really good
at handling datetime objects. For example, on our entity we have a created at field,
which is a DATETIME object. And you can see in our API if we, when we try our GET
endpoint, this is returned as a fancy DATETIME stream. The datetime normalizer is
what's responsible for doing that. There's also another really important normalizer
inside of API platform called the Object Normalizer. Its job is to read properties
off of an object so that tho then those properties can be normalized. To do that
internally it uses something, another component called the property accessor
components. And what's cool about the property accessor component is it's smart. So
for example,

If we look at our API here, you can see that when we make a get request to our
collections endpoint, one of the fields returns is name. But if we look at our SD
name is a private property, so how the heck is it reading our private property? The
answer is that the property accessor is smart. It first looks to see if the name
property is public and if it's not, it then looks for a GI name method. So this is
what's actually called when it's building the jsun from this object. The same thing
happens when we send Jsun N. The serializer looks at each field in the JSON and if
that field is settable like VA set name method, then it sets it and actually it even
a bit cooler than that. The serializer will even look for getter or setter methods
that don't correspond to any field. You can use this to create extra fields in your
API that don't even exist on your class. Check this out. Let's pretend that when we
are creating or editing a treasure, instead of sending a description field, we want
to be able to send a text description field

That contains line breaks and then we transform those line breaks into HTML break
text. Lemme show you what I mean below the set description method. Let's, let me
actually copy this, paste it, let's call it set text description. And it's basically
going to set the text description, but we're also going to call NL two BR on it. So
if you're not familiar, that just literally looks at any new lines and trans them,
forms them into line breaks. So when you refresh the documentation and open the post
or put end points immediately you can see we have a new field called text
description. The serializer saw the set text description method and so it now knows
that this is a settable property. You don't see it on the get end point. It's not
going to be returned though cause it doesn't have a getter. So it's a new field
that's only settable but not gettable or readable. All right, so let's actually try
this. Let me try my collection endpoint real quick so I can remember what IDs I have.
Perfect. I have treasure with ID one close this up. So let's try the put endpoint
two, do our first update. And when you do put endpoint, you don't have to send every
field, you can just send one in the field. So,

So I'll put well description there, actually include the /ins for that, represent new
lines and when we try it, perfect 200 status code and check this out. Description
field that has those HTML line breaks, those BR tAJAX in there. But I was expected no
text description method is returned. It's just a settable field. All right, so now
that we have this set text description, maybe that's the O, we want to make that the
only way that you can set that field. So let's just remove the set description
method. Now when we refresh and you look at the put endpoint, we still have text
description, but the description property is gone. The sea analyzer realizes that
that's no longer settable and so it removes it from our api. It would still be
returned cuz it's something that we can read but it's no longer writeable. So we just
worry about writing our class the way we want it. An API platform builds our API
accordingly. All right, let's see what else? Um, it's a little weird that we can set
the created that directly. That's usually kind of an internal field. So let's handle
that. So first up, let's finally created that field. And you know what I meant to
call this plundered at? I'm actually going to refactor that plundered at, it's going
to refactor the getter and setter for me. That's cool. And that's actually going to
change the column in my database. So I'm going to spin over here real quick.

Run Symfony console, make migration, and I'm going to look dangerously and just
execute that doctor and migrations migrate. What's bravery? Cool. And just by
renaming that if we refresh over here for exam, cool. Now it's called plunder dat in
our api. All right, so forget about the a p for a second. Let's just do a little
cleanup. The purpose of this plunder dat feel is for it to be set automatically
whenever we create a new dragon treasure. So let's create a construct method inside.
We'll say this->plundered at = new date time immutable. And now we don't need the =
nu. And if we search for a set plundered at, we don't really need that field either,
so I'm going to remove it. This now means that the plundered at property is readable
but not writeable. So no surprise when we open up our put endpoint or our post
endpoint plundered at is gone. But if we look at what our model would look like if we
Reddit plundered at is still there. All right, one more goal, I'm going to add a one
more fake field called plundered at a go. When we read our resource that returns a
human readable version of the of the date like two months ago. So to do this, we're
going to install a library called SBOT /Carbon.

It's a handy library for working with dates and times. Cool. And then finally get
plundered at method. I'll copy that, duplicate it below, but now it's going to be
returning a string and we'll call it get plundered at a go. And now we're actually
going to return carbon instance. This->plundered at->if for humans, super cool
method. So there is no plundered at a go property, but the serializer should see this
as a readable getter and expose it as a property. But while we're here, I'm also
going to add a little documentation above this that describes its its meaning. All
right, let's try this. So as soon as we refresh and open up, for example, I get
endpoint, we see it there under the example configuration. Um, by the way, one other
way to see the fields that you have is down here in these schema. So you can see we
have PL at, and if we open that up, you can see it's there and you can see it's read
only true. Same thing down here for the other one. And up here, if we try it, I'll
try to get end point with ID one. We got it six days ago, how cool is that? All
right. Next,

What if we do want to have a certain getter or setter method in our class, like set
text description, but we do not want that to be part of our api. The answer is
serialization groups.
