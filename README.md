#Pimcore Migrations Plugin

## Introduction
Pimcore already does a lot of migration work for developers, all class definition changes and database schema changes can
be easily ported between development, staging and production environments. However, the following use-cases for database
changes are not easily portable between Pimcore environments: 
 - Custom persistant model schemas.
 - The addition of new class definitions does not happen automatically happen in deployment.
 - Changes to website settings (stored in the database) do not happen in any deployment helpers.
 - Alterations to document editables, i.e. changing a textarea to wysiwyg creates invalid references to editables.
 - A rename of a ClassDefinition field removes the old column, then adds a new one, rather than actually renaming.

### What this plugin provides
This plugin provides a simple mechanism for implementing version controllable database changes between pimcore environments,
in a lightweight convention similar to packages such as Phinx or Doctrine Migrations.

## Installation

## Configuration

## Usage

