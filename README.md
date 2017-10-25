# SpecShaper GDPR Bundle

A bundle to aid with the General Data Protection Regulation requirements. 

Features include:

- Written for Symfony verison 3.x.x
- Provides annotation for adding to entity parameter doc blocks
- Records values for Data Protection Impact Assesments of entity parameters.
- Uses SpecShaper\EncryptBundle to encrypt senstive data


**Warning**
- This bundle has not been unit tested.
- It has only been running on a Symfony2 v3.0.1 project, and not backward
compatibility tested.

Features road map:

- [ ] Generate a entity parameter coverage report.
- [ ] Generate a summary report of all results.
- [ ] Figure out how to dispose of data at end of retention period.


## Documentation

The source of the documentation is stored in the `Resources/doc/` folder
in this bundle.

## License

This bundle is under the MIT license. See the complete license in the bundle:

    Resources/meta/LICENSE

## About

GdprBundle has been written for the [SpecShaper](http://about.specshaper.com) and [Parolla](http://parolla.ie) websites
to encode users private data.

## Reporting an issue or a feature request

Issues and feature requests are tracked in the [Github issue tracker](https://github.com/mogilvie/HelpBundle/issues).

When reporting a bug, it may be a good idea to reproduce it in a basic project
built using the [Symfony Standard Edition](https://github.com/symfony/symfony-standard)
to allow developers of the bundle to reproduce the issue by simply cloning it
and following some steps.

# Installation

## Step 1: Download the bundle

Open a command console, enter your project directory and execute the
following command to download the latest version of this bundle:

```
$ composer require specshaper/gdpr-bundle dev-master
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

## Step 2: Enable the bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `app/AppKernel.php` file of your project:

```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...
            new SpecShaper\GdprBundle\SpecShaperGdprBundle(),
        );
        // ...
    }
    // ...
}
```

## Step 2: Configure the bundle

Geneate a 256 bit 32 character key and add it to your parameters file.

```yaml
// app/config/parameters.yml

    ...
    encrypt_key: <your_key_here>
    
```

Configure the EncryptBundle to use the GdprBundle encryption subscriber
```yaml
// app/config/config.yml

    ...
    spec_shaper_encrypt:
        is_disabled: false
        subscriber_class: 'SpecShaper\GdprBundle\Subscribers\GdprSubscriber'

```   
You can disable encryption of the database by setting deleting is_disabled or setting it true.

## Step 3: Create the entities
Add the Annotation entity to the declared classes in the entity.

```php
<?php
...
use SpecShaper\GdprBundle\Annotations\PersonalData;
```

Add the annotation '@PersonalData' to the parameters that you want to record.

```
    /**
     * @var \DateTime
     *
     * @PersonalData(
     *     isSensitive=false,
     *     isEncrypted=true,
     *     identifiableBy="Can be associated using other records",
     *     providedBy="The employee, revenue, the employer",
     *     purposeFor="Used to check the employee is not under age",
     *     retainFor="6 years",
     *     disposeBy="Aggregate into decades"
     * )
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $dateOfBirth;
   
```

For DateTime parameters store the date as a string, and use the getters and setters
to convert that string.

You may also need to create a DataTransformer if you are using the parameter in a form
with the DateType formtype.


## Step 4: Decrypt in templates

If you query a repository using a select method, or get an array result 
then the doctrine onLoad event subscriber will not decyrpt any encrypted
values.

In this case, use the twig filter to decrypt your value when rendering.

```
{{ employee.bankAccountNumber | decrypt }}
```

# Reports
