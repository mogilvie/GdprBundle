# Features
## Annotations
Provide a custom annotation @PersonalData for each class property considered to be personal data:
- Ignore properties that map to other classes, we are only interested in stored data.

The annotation class itself shall have the following properties for setting:
- purpose: Why is this data being collected?
- isSensitive: True if he property is classified as sensitive, such as regligion, health, political
- method: The method by which this data is personal. Directly, by association
- encrypt: True if the data should be encrpted
- providedBy: Who / what provides the data
- sharedWith: An array of third parties who the data is possibly shared with
- retainFor: How long the data is to be kept for, in PeriodInterval format
- disposeBy: What is done with the data when it is no longer required/relevent. (Delete / Aggregate / Anonymise)

## Encryption
The SpecShaperEncryptBundle will be used to encyrpt any parameters where the annotation parameter encrypt is true.
- Extend the encrypt bundle subscriber and replace it
- The gdpr subscriber to override the select annotation function and place a check on the encrypt property.

## Consent
Provide a consent form and store records of the users consent to share data.

## Reporting
### Coverage Review
The bundle should produce a summary of all parameters in the dabase and identify which are classified as unclassified, Personal 
or Special.  

### Detail Report
A report should list all personal data and its parameters as stored by the annotation.
