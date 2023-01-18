# Serialization Tricks

Coming soon...

We've kind of tricked the system to allow a text description field when we send data.
This of course, is made possible thanks to our set text description method, which we
added so that we could run NL two b r on any description that is sent to our api. So
this means that the user sends a text description field when they're editing or
creating a treasure, but then when they're reading, we send back a field called
description and that's totally legal. You are allowed to have different input fields
versus output fields, but it would be a little cooler in this case if both of these
were called description. So the question is, can we control what the name of a field
is in the serializer? The answer is absolutely not surprisingly, via another
attribute. This one is called serialized name. And we can pass description. This
won't change how the field is read, but now if we refresh this page and look at the
put endpoint, yep, we can now send description. All right, what about structor
arguments in our N entity? Because one, for example, when we make a post request, we
know it's using our

Setr methods to set all these properties. So let's see what happens if we remove the
set method. So find set name and let's remove this and instead find the constructor
and we'll add a string name argument there. And then this arrow name equals name. If
you think about it from an object-oriented perspective, this field can't can be set
when the object is created, but after that it's read only. Heck, if you wanted to get
really fancy, you could even add read only to the property right now. So it's read
only inside of php. And

Let's

See how it looks in our documentation. If you open up the post request, it looks like
we can still send a name field. So let's actually try this. Let's hit try it out.

Let's

Talk about that giant slinky. We got cool factor eight and let's see what happens.
Hit execute and it worked. And you can see for the response that the name was set,
how is that possible? If you go down and look at the put end point, you'll see that
it does advertise name here, but let's actually see my ID was four. So let's try this
out. Say ID four. And when you execute here, it doesn't change. So it's read only in
our code and it's also read only in our api. But how did the post request work at
all? If we don't have a setter, the answer is that the serializer is smart enough to
set construct arguments, but the matching is done by name. So the fact that this
argument is called name and the property is called name is what makes this work like
watch, check this out. Let's change this argument to treasure name in both spots.
Announcement over refresh and check out our post endpoint. The field is gone. APM
platform sees that we have a treasure name field that could be, that could be sent,
but since treasure name doesn't correspond to any property, that field doesn't have
any serialization groups. And so it's not actually used. So I'm gonna change that
back to name. So by using name, it goes and looks at the name property and reads and
uses the serialization groups off of it to figure out if it should be set.

Now there's still one other problem with Structor arguments that I want you to be
aware of. So hit try it out on the post endpoint again.

Actually let me refresh to make sure we have fresh documentation. So let's pretend
that we, what happens if the U, our user actually doesn't pass a name at all and they
execute? You can see it airs with a 400 status code, but it's not a very good error.
It says cannot create an instance of dragon treasure from serialized data because
it's construction requires a name parameter. So not a great error they wanna show you
to use. Or what you really want is to allow validation to take care of that. We're
gonna talk about validation in a little while, but in order for validation to work,
the serializer needs to be able to actually do its job. So in this case, what I'm
gonna do is just allow the name argument to optional and then in a few minutes we're
gonna talk about validation and we'll add validation to make sure that's set.

But that's gonna give us a much nicer validation error than this error right here. So
if we try that again, right now it's actually a worse error. It's a 500 error cause
it fails in the database. But the point is it allowed our object to be created. And
in a few minutes, once we add Val validation rules, you'll see the really nice air
that we get here. All right, next to help us while we're developing, let's add a rich
set of data fixtures, then we are gonna enjoy and play with a great feature that API
platform gives us for free Page Nation.

