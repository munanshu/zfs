<?php

namespace Admin\Service;


class CustomaclService
{

	protected $modulemapper;

	public function __construct(ModuleMapper $modulemapper)
	 {
	 	$this->modulemapper = $modulemapper;
	 	$this->modulemapper->fetchAllModules();
	 }



}