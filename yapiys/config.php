<?php


//Initializes the virtual directory App
VirtualApp::initialize();


//Initializes the virtual Yapiys Directory
VirtualYapiys::initialize();


/*
 * Loads all plugins
 */

PluginsManager::loadAll();

/*
 * Loads all libraries
 * Libraries are allowed to load their Base Classes versions
 */
Framework::loadAllLibraries();
