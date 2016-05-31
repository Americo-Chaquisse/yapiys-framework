<?php

class CLIDispatcher {


    /**
     * Dispatches a command execution
     * @param $arguments arguments passed via CLI
     */
    public static function dispatch($arguments){
   
        $cmdName = false;
        $module = false;
        $params = false;
        $firstParamIndex = 0;
        $commandParams = false;
        $proceed = false;
        
        //Parameters specified
        if(count($arguments)>=2){
            
            $option1 = $arguments[1];
            
            //Executing module command
            if($option1==='--m'){
                
                //Params count shoud be greater than 4
                if(count($arguments)>=4){
        
                    $module = $arguments[2];
                    $cmdName = $arguments[3];
                    $firstParamIndex = 4;
                    
                    //Gets module command parameters
                    $commandParams = self::getCommandParams($module,$firstParamIndex,$arguments);              
                    $proceed = true;            
                    
                }else{
                    

                    //Module command params where not satisfied
                    echo "error: Invalid number of params do module command execution\n";
                    
                }
                
            }else{

                //Application root command
                
                $module =false;
                $cmdName = $arguments[1];
                $firstParamIndex = 2;
                
                
                //Gets root command parameters
                $commandParams = self::getCommandParams($module,$firstParamIndex,$arguments); 
                $proceed = true;
                 
                
            }   
                    
        }
        
        
        //run command
        if($proceed){
              
            self::run($module,$cmdName,$commandParams);
        }
        
    }

    /**
     * Calls a command function
     * @param $module app module name
     * @param $command app command name
     * @param $params parameters to be passed to the command.
     */
    private static function run($module,$command,$params){


        //Command PHP Class File path
        $file = false;


        if($module){//is a module command

            //
            $file = APPLICATION_DIR.MODULES_DIR.'/'.
            strtolower($module).'/'.COMMANDS_DIR.ucfirst($command).'Shell.php';

            
        }else{//Is a root command


            //Gets the file full path from VirtualApp Directory
            $file = VirtualApp::full_path('commands/'.ucfirst($command).'Shell.php');
  
        }


        //Valid file
        if($file){

            //Command file exists
            if(file_exists($file)){

                
                
                 include $file; 
                 $class_name = ucfirst($command).'Shell';
                

                 //Basic command
                 if(is_a($class_name, 'AppShellCommand',true)){
                     
                                       
                     
                        //Main method does not exists
                        if(!method_exists($class_name,'main')){
                            
                            echo "error: Shell command class does not have method main\n";
                            return;
                                
                        }
                     
                        $shell_instance = new $class_name(); //Instantiate the command class.

                        /*
                         * Passed params count should be less than function total params and
                         * Should be greater or equal to required params
                         */
                        if(self::commandParamsOk($class_name,'main',$params)){

                            call_user_func_array(array($shell_instance, "main"),$params);
                            
                        }else{//Wrong number of parameters
                            
                            echo "error: Wrong parameters number passed to shell command\n";
                            
                        }
                         
                 //Extended command
                 }else if(is_a($class_name, 'AppExtendedShellCommand',true)){
                    
                     //No params passed from CLI
                     if(count($params)==0){
                         
                         echo "error: Shell command function name not specified\n";
                         return;
                         
                     }
                     
                     //Command to execute
                     $command_name = $params[0];
                     unset($params[0]);
                     
                     
                     //Command function not found
                        if(!method_exists($class_name,$command_name)){
                            
                        echo "error: Shell command class does not have de specified function\n";
                        return;
                                
                        }
                     
                        
                     
                        $shell_instance = new $class_name();


                        if(self::commandParamsOk($class_name,$command_name,$params)){
                            
                            //Calls the command function
                            call_user_func_array(array($shell_instance, $command_name),$params);
                            
                        }else{
                            
                            echo "error: Wrong parameters number passed to shell command function\n";
                            
                        }
                     
                     
                     
                 }else{
                    
        echo "error: The Shell command class does not extend AppShell neither ExtendedShell\n";
                     
                 }
                
            }else{//Command file not found



                echo "error: Shell command not found: $file \n";
            }
                         
        }
        
    }


    /**
     * Checks if the params passed are Ok.
     * @param $class_name The command class name.
     * @param $command_name The command name.
     * @param $params params passed to the command
     * @return bool Returns true if the passed params count matches to the required parameters count.
     */
    private function commandParamsOk($class_name,$command_name,$params){

                        /*
                         * Passed params count should be less than function total params and
                         * Should be greater or equal to required params
                         */
        $shell_main_function_reflection = new ReflectionMethod($class_name,$command_name);
        $total_params_required = $shell_main_function_reflection->getNumberOfRequiredParameters();
        $total_params = $shell_main_function_reflection->getNumberOfParameters();

        return (count($params)>=$total_params_required&&count($params)<=$total_params);


    }
    
    private static function getCommandParams($module,$startAt,$arguments){
        $params_total = 0;
        $cmdParams = array();
        
            if($module){
                
                $params_total = count($arguments)-4;
                
            }else{
                
                 $params_total = count($arguments)-2;
                
            }
        
        if(count($arguments)<=$startAt){
            
        }else{
            $cmdParams = array_slice($arguments,$startAt,$params_total);
        }
        
        return $cmdParams;
    }
    
    
}