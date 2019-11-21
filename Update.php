<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
 */
namespace Arikaim\Core\System;

use Arikaim\Core\System\System;
use Arikaim\Core\System\Composer;

/**
 * Update Arikaim core
 */
class Update 
{
    /**
     * Update Arikaim core package
     *
     * @return bool
     */
    public function update()
    {
        $errors = 0;
        $output = Composer::updatePackage(System::getCorePackageName(),true);
      
        return ($errors == 0); 
    }

    /**
     * Return core package info
     *
     * @return string
     */
    public function getCoreInfo()
    {
        return Composer::runCommand('show ' . System::getCorePackageName());
    }

    /**
     * Return array with code packages
     *
     * @param integer $resultLength Result maximum lenght
     * @return array
     */
    public function getCorePackagesList($resultLength = null)
    {
        $packageInfo = Composer::getPackageInfo("arikaim","core");
        $list = $packageInfo['package']['versions'];
        unset($list['dev-master']);
        $packages = [];
        $count = 0;       
        
        foreach ($list as $package) {          
            $info['version'] = $package['version'];
            $info['name'] = $package['name'];
            array_push($packages,$info);
            $count++;
            if (($resultLength != null) && ($count >= $resultLength)) {               
                return $packages;
            }
        }

        return $packages;
    }
}
