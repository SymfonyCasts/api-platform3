# Context Builder

Coming soon...

In dragon Treasure finally is published field. All right, remember earlier we added this API property security thing so that this field is only shown to admin users or or owners of this object. This is a simple and 100% valid way to handle this situation. However, there is another way to handle dynamic fields based on the user in it may or may not have two advantages depending on your situation. First, check out the documentation, open the get endpoint for a single dragon treasure and even without trying it, you can see down here that is published is something that is advertised in our documentation. Depending on the field that might be perfect or not. If is published as truly an internal admin only field, we might not want that advertised to the world

So that the fact that is published is in the documentation is either a pro or a con of this approach depending on your situation. The second possible problem with this is that if you had this security on many different properties inside of your class, it's gonna have to run that security a lot of times to return a collection of objects. It's possible I wouldn't worry about that too much, but it's possible that could cause some performance issues. So to solve these two possible problems, I wanna show you an alternative solution. We're gonna replace this API property thing and instead give this field two new groups instead on groups. We're not gonna use treasure read and write cause we don't want it to always be exposed. We're gonna use admin read and admin rights so this won't work yet, but the idea is that if the current user is an admin user, then when we serialize, we'll serialize with these two additional groups. Unfortunately right now groups are static. They're something that we set way up here in our API resource and that's it. But we can make them dynamic internally. API platform has a system called a context builder, which is responsible for building the normalization context or demonalization context. We can hook into that to dynamically add our own groups.

Here's how it looks over in, how about source API platform? Let's create a new class called admin groups context builder and we're gonna make this implement CER contact builder interface. Then I'll go to code generate or command n go to implement methods to create the one we need, which is called create from request. So pretty simple. Uh, if API platform's gonna call this, pass us the request whether or not we're normalizing or de normalizing and then we're gonna return an array of the context that should be passed the serializer. Now like we've seen with a few other things, our intention is not to replace the core context builder. Rather we want the core context builder to run and then we will do something on top of that. So to do this we're gonna use once again service decoration so we know how this works. We add a construct method that takes a private serializer context builder interface and I'll call this decorated perfect. And then down here we'll call that say context equals this arrow, decorated arrow create from request passing, request normalization and extra attributes, extracted attributes. Then I'll do my classic dump message to make sure this is working

And we'll return context now to tell a symphony to use our context builder in place of the real one we'll add our ASEC peer and this is where we need the service ID of whatever the core context builder is. That's something you can find in the documentation. It is API platform dot serializer dot context underscore builder. Now two quick things if you're using, be careful when you use a serializer context builder, there's actually two of them. Um, and one of them is from GraphQL. Make sure you get the one that is just from API platform serializer, unless you are using the GraphQL, then use that one. And if you're using GraphQL then the service ID has a little extra graph QL in the middle anyways, that should hopefully get be enough to get this to work and see our dump.

So actually let's run all of our tests real quick. I wanna see which ones fail and, okay, cool. You see our dump message a bunch of times and then we see two failures. So this one here we have test admin can patch to edit treasure. That's the one we're working on right now. We're gonna worry about test owner can see is published field in a minute blank. How, let me copy this test method name and let's rerun that with dash dash filter equals and perfect. We see our dump and we actually see it three times, which is interesting. The context is actually created three times. It's created first at the start of the request. It creates the context when it's loading our dragon treasure from the database. So, uh, let's actually open this test up so I can show you what we're looking at here.

Perfect. So we're making a patch request to slash aps slash treasure slash one. So the context builder is actually called one time when it's querying and loading that dragon treasure. It's kind of an odd situation because the context is meant to be used for the serializer but we're not at that point. It's just querying for the dragon treasure. It's not actually serializing it and that's then called a second time when the jsun that we're sending is being de-normalized into the object and then it's called the third time when our finished dragon treasure is being returned to the user as json. Anyways, we can now hop in here and add our dynamic groups. So we need to determine if the user is an admin or not. So step one is that we're gonna need to add a second constructor argument, private security, the one from security bundle called security. And then down here I'm first gonna say if is set context groups. So groups is the key that stores the serialization groups

And this arrow, security arrow is granted roll admin. Then we're gonna add the users. So we can add them by saying context left score bracket groups left square back, right square bracket equals and then we have to be careful here we use normalization. If we are normalizing right now, then we're gonna add admin colon read else, we're gonna add admin colon, right? So we wanna make sure that our groups, if we are actually normalizing, we're reading it. We only add admin read if we are de normalizing. We only add admin colon wright. Now the reason I'm checking for is set context groups. This doesn't apply to our project right now, but if we were serializing an object that didn't have any groups on it, then adding these groups would actually cause it to return less fields. Remember, if there's no serialization groups, then the serializer just returns returned serializes every single field. But as soon as you add one group, then it only serializes the things in that one group. So if there aren't any groups on here, we're not gonna do anything. We're gonna let everything be serialized or dec serialized normal. But like I said, we're using uh, normalization groups on everything. So that doesn't really apply to us.

All right, so now let me try that test. It passes. So as I mentioned earlier, so this means that we do have an is published field that's coming back if we're an admin user. But as I mentioned earlier, if you refresh the documentation and open the get one treasure endpoint, you're not gonna see is published down here. So that might be a good thing or might be a bad thing. It is technically possible to make these docs dynamic so that actually loads correctly. If you're logged in as an admin, it would show that field and if you're not, it wouldn't. That's not something that we're gonna attack on this tutorial. We did talk about it in our APM platform two tutorial, but the configuration system changed. All right, next, let's look down at the next method, which tests to see that an owner can see the is published field. This is also currently failing and it's actually even trickier than the other situation because we need to include the is published field on an object by object basis.