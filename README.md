####  About

This module enables messages to be grouped into threads or conversations.  For instance,
it is used with the message_private module to create conversations between individuals and groups of individuals.

The module keeps track of who is (and was) in the conversation
The module enables you to add fields such as title to a conversation
The module enables an individual to have simultaneous conversations with different people and keep the conversations
separate in the inbox

####  Requirements

Message stack modules including:
Message
Message Private 


####  Installation

Install module as normal or using composer or drush


####  Configuration

When installed, along with the Private Message module:
The module creates a default message thread bundle called Conversation
On the /user page there should be a tab called Conversations
Click on that and you can start creating a conversation
Once the conversation is created, send a message
All messages sent within that conversation are shown on the conversation page
Set permissions to allow roles to create message threads and private messages

