# dynamic-form-bundle

[![Build Status](https://travis-ci.org/barbieswimcrew/dynamic-form-bundle.svg?branch=master)](https://travis-ci.org/barbieswimcrew/dynamic-form-bundle)

## Goals
The symfony/form Component is great for creating HTML Forms with PHP backed validation while providing a super easy fluent interface to the developers who use and love it.

The manual even shows some advanced ways of bringing dynamic behaviours to your forms, such as "Customizing your Form based on the Underlying Data", "How to dynamically Generate Forms Based on user Data" or "Dynamic Generation for Submitted Forms".

But despite there are mechanisms for making your forms dynamic on the php side - we saw a lack of functionality on the frontend. And that's what this project is for.

The Dynamic-Form-Bundle gives you an standardized way to make your form really dynamic. In the first step it will allow you to show or hide a one or more Form-Fields by clicking a Checkbox/Radiobutton that is configured to do exactly that right within its FormType. That affects Javascript and Form-Validation.

We hope this Bundle makes a developer's live easier ;)

With best regards, your Barbieswimcrew and Jidoka1902

## Usage as bundle in symfony projects
The whole bunch of files need to be placed into the folder `src/Barbieswimcrew/Bundle/DynamicFormBundle` 
After that register the bundle to your AppKernel by `new Barbieswimcrew\Bundle\DynamicFormBundle\DynamicFormBundle()`
