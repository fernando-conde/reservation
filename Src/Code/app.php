<?php
namespace DbSync;

class App
{

    /** BackOffice */
    private $bo;

    /** @var FrontOffice */
    private $fo;

    /** @var int */
    private $start;

    /** @var int */
    private $step;

    /** @var bool */
    private $export = true;

    public function __construct()
    {        
        // Start
        header('Content-type: text/html; charset=utf-8');
        header('X-Accel-Buffering: no');

        $this->start = microtime(true);

        ob_start();
        ob_clean();

        $content = '<!DOCTYPE html><html><head><title>DB Sync</title><link rel="stylesheet" type="text/css" href="main.css"><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head><body>';
        $content .= '<h1>DB SYNCHRONISATION BACKOFFICE => FRONTOFFICE</h1>';
        $this->display($content);

        ini_set('memory_limit', '-1');
        set_time_limit(0);

        // Parameters
        $filterOrigin = null;
        $filterDestination = null;
        
        // FO

        // $queryOrigin = 'TB_MAI_PAGE';
        // $queryDestination = 'TB_MAI_PAGE';
        // $keys = ['ID'];

        //$queryOrigin = 'TB_MAI_WORDING';
        //$queryDestination = 'TB_MAI_WORDING';
        //$keys = ['ID'];

        // MAIN

        // $queryOrigin = 'TB_MAI_COUNTRY';
        // $queryDestination = 'TB_MAI_COUNTRY';
        // $keys = ['ID'];

        // SPORT

        // $queryOrigin = 'TB_MAI_SPORT';
        // $queryDestination = 'TB_MAI_SPORT';
        // $keys = ['ID'];

        // $queryOrigin = 'TB_SPO_COMPETITION';
        // $queryDestination = 'TB_SPO_COMPETITION';
        // $keys = ['ID'];

        // $queryOrigin = 'TB_SPO_ROUND';
        // $queryDestination = 'TB_SPO_ROUND';
        // $keys = ['ID'];

        // $queryOrigin = 'TB_SPO_GROUP';
        // $queryDestination = 'TB_SPO_GROUP';
        // $keys = ['ID'];

        // $queryOrigin = 'TB_SPO_DAY';
        // $queryDestination = 'TB_SPO_DAY';
        // $keys = ['ID'];

        // $queryOrigin = 'SP_SPO_MATCH';
        // $queryDestination = 'TB_SPO_MATCH';
        // $keys = ['ID'];

        $queryOrigin = 'TB_SPO_TEAM';
        $queryDestination = 'TB_SPO_TEAM';
        $keys = ['ID'];

        // $queryOrigin = 'TB_SPO_FEATURE';
        // $queryDestination = 'TB_SPO_FEATURE';
        // $keys = ['ID'];

        // $queryOrigin = 'TB_SPO_FEATURE_PERSON';
        // $queryDestination = 'TB_SPO_FEATURE_PERSON';
        // $keys = ['ID'];

        // FOOT

        //$queryOrigin = 'SP_SPO_FOO_TEAM';
        //$queryDestination = 'TB_SPO_FOO_TEAM';
        //$keys = ['REF_GROUP', 'REF_TEAM'];

        // $queryOrigin = 'TB_SPO_FOO_COMPETITION';
        // $queryDestination = 'TB_SPO_FOO_COMPETITION';
        // $keys = ['ID'];
        // $filterOrigin = 'ID=2';
        // $filterDestination = 'ID=2';

        // $queryOrigin = 'SP_SPO_FOO_MATCH';
        // $queryDestination = 'TB_SPO_FOO_MATCH';
        // $keys = ['ID'];
        // $filterOrigin = '@REF_COMPETITION=70';
        // $filterDestination = 'REF_COMPETITION=70';
        // $filterOrigin = '@ID=58093';
        // $filterDestination = 'ID=58093';

        $this->display('<h2>Table: '.$queryDestination);

        // Get data
        $destination = $this->getDestination($queryDestination, $filterDestination);
        $countDestination = count($destination);
        $this->display('<h2>Nombre d\'enregistrements FO: '.count($destination).'</h2>');
        $this->displayTime();

        $origin = $this->getOrigin($queryOrigin, $filterOrigin);
        $this->display('<h2>Nombre d\'enregistrements BO: '.count($origin).'</h2>');
        $this->displayTime();

        // Compute
        list($toDoubleDestination, $keysDiff) = $this->computeDestination($destination, array_keys(current($origin)), $keys);
        $toDoubleOrigin = $this->computeOrigin($origin, $keys);
        $this->display('<h2>Compute OK</h2>');
        $this->displayTime();

        // Analyse
        list($toUpdate, $toInsert, $toNothing, $toDelete) = $this->analyze($origin, $destination, $keys);
        $this->display('<h2>Analyse OK</h2>');
        $this->displayTime();

        unset($origin);
        unset($destination);

        $content = '<h2>Opération à venir : Insertion <b class="insert">'.count($toInsert).'</b>';
        $content .= ' - Mise à jour <b class="update">'.count($toUpdate).'</b>';
        $content .= ' - A jour <b class="nothing">'.count($toNothing).'</b>';
        $content .= ' - Doublons Fo <b class="delete">'.count($toDoubleDestination).'</b>';
        $content .= ' - Doublons Bo <b class="delete">'.count($toDoubleOrigin).'</b>';
        $content .= ' - Suppression <b class="delete">'.count($toDelete).'</b> ('.($countDestination+count($toInsert)-count($toDelete)-count($toDoubleDestination)).')</h2>';
        $this->display($content);        
        $this->displayTime();

        // Surplus de colonnes
        if (count($keysDiff) > 0) {
            $this->display('<h2>Surplus de colonnes</h2>');
            dump($keysDiff);
        }

        // Doublons
        if (count($toDoubleDestination) > 0) {
            $this->display('<h2 class="delete">Doublons Fo</h2>');
            dump($toDoubleDestination);
        }

        if (count($toDoubleOrigin) > 0) {
            $this->display('<h2 class="delete">Doublons Bo</h2>');
            dump($toDoubleOrigin);
            foreach ($toDoubleDestination as $line) {
                dump(array_diff_assoc($line[0], $line[1]));
            }
        }

        // Exports
        $this->exportDelete($toDelete, $toDoubleDestination, $keys);
        $this->exportInsert($toInsert);
        $this->exportUpdate($toUpdate, $keys);

        $this->display('<h2>-- Terminé --</h2></body></html>');

        ob_end_clean();
    }

    /**
     * @param array $origin
     * @param array $destination
     * @param array $keys
     * @return array
     */
    private function analyze(array $origin, array $destination, array $keys)
    {        
        $toUpdate = [];
        $toInsert = [];
        $toNothing = [];
        $toDelete = [];
        foreach ($origin as $ko => $line) {
            $key = $this->getKey($line, $keys);

            if (isset($destination[$key]) === false) {
                $toInsert[] = $line;
            } else {
                $columns = array_diff_assoc($line, $destination[$key]);
                if (count($columns) === 0) {
                    $toNothing[] = $key;    
                } else {
                    $toUpdate[] = array_merge($columns, $this->getKeys($line, $keys));
                }
                unset($destination[$key]);
            }
        }        

        foreach ($destination as $line) {
            $toDelete[] = $this->getKeys($line, $keys);
        }    
        
        return [$toUpdate, $toInsert, $toNothing, $toDelete];
    }

    /**
     * @param array $toDelete
     * @param array $toDouble
     * @param array $keys
     */
    private function exportDelete(array $toDelete, array $toDouble, array $keys)
    {                 
        $toDelete = array_merge($toDelete, $toDouble);    
        if (count($toDelete) > 0) {
            $this->display('<h3 class="delete">Démarrage Suppression Doublons+Supressions</h3>');   
            $request = $this->fo->prepareDelete($keys);
            foreach ($toDelete as $index => $line) {
                if ($this->export === true) {
                    $result = $request->execute($this->getKeys($line, $keys));
                    $this->displayOk($result, $index);
                }
            }
            $this->display('<h3 class="delete">Doublons & Suppressions ok</h3>');  
            $this->displayTime();
        }
    }

    /**
     * @param array $toInsert
     */
    private function exportInsert(array $toInsert)
    {  
        if (count($toInsert) > 0) {
            $this->display('<h3 class="insert">Démarrage Insertion</h3>'); 
            $request = $this->fo->prepareInsert(array_keys(current($toInsert)));
            $error = [];
            foreach ($toInsert as $index => $line) {                
                if ($this->export === true) {
                    try {
                        $result = $request->execute($line);
                        $this->displayOk($result, $index);
                    } catch (\PDOException $e) {                        
                        $error[] = $line['ID'].' : '.$e->getMessage();
                    }
                }
            }

            if (empty($error) === false) {
                dump('INSERT ERRORS', $error);
            }

            $this->display('<h3 class="insert">Insertion ok</h3>');  
            $this->displayTime();
        }
    }
    

    /**
     * @param array $toUpdate
     * @param array $keys
     */
    private function exportUpdate(array $toUpdate, array $keys)
    {     
        if (count($toUpdate) > 0) {
            $this->display('<h3 class="update">Démarrage Mise à jour</h3>');    
            $error = [];    
            foreach ($toUpdate as $index => $line) {
                $request = $this->fo->prepareUpdate(array_keys($line), $keys);
                if ($this->export === true) {  
                    try {
                        $result = $request->execute($line);
                        $this->displayOk($result, $index);
                    } catch (\PDOException $e) {
                        $message = $e->getMessage();
                        $list = explode("'", $message);
                        if (isset($list[1]) === true && isset($line[$list[1]]) === true) {
                            $message .= ' - value: '.$line[$list[1]];
                        } else if (isset($list[3]) === true && isset($line[$list[3]]) === true) {
                            $message .= ' - value: '.$line[$list[3]];

                        }
                        $error[] = $line['ID'].' : '.$message;
                    }
                }
            }

            if (empty($error) === false) {
                dump('UPDATE ERRORS', $error);
            }

            $this->display('<h3 class="update">Mise à jour ok</h3>');  
            $this->displayTime();
        }
    }

    /**
     * @param string $query
     * @return array
     */
    private function getOrigin($query, $filter = null)
    {
        $this->bo = new BackOffice();
        $origin = $this->bo->get($query, $filter);

        return $origin;
    }

    /**
     * @param string $query
     * @param array $keys
     * @return array
     */
    private function getDestination($query, $filter = null)
    {
        $this->fo = new FrontOffice();
        $destination = $this->fo->get($query, $filter);

        return $destination;
    }

    /**
     * @param array &$destination
     * @param array $keysOrigin
     * @param array $keys
     * @return array
     */
    private function computeDestination(array &$destination, array $keysOrigin, array $keys)
    {
        $toDouble = [];
        $keysDiff = [];
        if (count($destination) > 0) {
            $keysDestination = array_keys(current($destination));

            $keysDiff = [];        
            if (count($keysOrigin) !== count($keysDestination)) {
                $keysDiff = array_diff($keysDestination, $keysOrigin);
            }

            $destinationIndex = [];
            foreach ($destination as $line) {
                $key = $this->getKey($line, $keys);

                if (isset($destinationIndex[$key]) === false && isset($toDouble[$key]) === false) {
                    foreach ($keysDiff as $keyToDelete) {
                        unset($line[$keyToDelete]);
                    }

                    $destinationIndex[$key] = $line;
                } else {
                    if (isset($toDouble[$key]) === false) {
                        $toDouble[$key] = [];
                    }
                    $toDouble[$key][] = $line;
                    
                    if (isset($destinationIndex[$key]) === true) {
                        $toDouble[$key][] = $destinationIndex[$key];
                        unset($destinationIndex[$key]);
                    }
                }
            }

            $destination = $destinationIndex;
        }
        return [$toDouble, $keysDiff];
    }

   /**
    * @param array &$destination
    * @param array $keys
    * @return array
    */
   private function computeOrigin(array &$origin, array $keys)
   {
       $toDouble = [];
       if (count($origin) > 0) {
           $originIndex = [];
           foreach ($origin as $line) {
                $key = $this->getKey($line, $keys);

                if (isset($originIndex[$key]) === false && isset($toDouble[$key]) === false) {
                    $originIndex[$key] = $line;
                } else {
                    if (isset($toDouble[$key]) === false) {
                        $toDouble[$key] = [];
                    }

                    if (isset($originIndex[$key]) === true) {
                        $toDouble[$key][] = array_diff($line, $originIndex[$key]);
                        $toDouble[$key][] = $originIndex[$key];
                        unset($originIndex[$key]);
                    } else {
                        $toDouble[$key][] = $line;
                    }

               }
           }

           $origin = $originIndex;
       }

       return $toDouble;
   }

    /**
     * @param array $line
     * @param array $keys
     * @return array
     */
    private function getKeys(array $line, array $keys)
    {
        $return = [];
        foreach ($keys as $key) {
            $return[$key] = $line[$key];
        }

        return $return;
    }

    /**
     * @param array $line
     * @param array $keys
     * @return string
     */
    private function getKey(array $line, array $keys)
    {
        return implode('-', $this->getKeys($line, $keys));
    }

    /**
     * @param string $content
     */
    private function display($content)
    {    
        echo $content;
        ob_flush();
        flush();
    }

    private function displayTime()
    {
        $step = microtime(true);
        $return = $this->computeTime($step - $this->start);

        if (isset($this->step) === true) {
            $return .= ' (+'.$this->computeTime($step - $this->step).')';
        }

        $this->step = $step;

        $this->display('<span class="time">'.$return.'</span>');
    }

    /**
     * @param bool $result
     * @param int $index
     * @return string
     */
    private function displayOk($result, $index)
    {
        $index++;
        $content = 'x';
        if ($result === true) {
            $content = '.';
        }
        if ($index%10 === 0) {
            $content .= ' '.$index.' ';
        }
        $this->display($content);
    }

    /**
     * @param int $time
     * @return string
     */
    private function computeTime($time)
    {
        $time = $time * 1000;

        $return = '';
        $list = ['h' => 3600000, 'm' => 60000, 's' => 1000, 'ms' => 1];
        foreach($list as $unit => $seconds) {
            $$unit = floor($time/$seconds);            
            $time = $time%$seconds;       
            
            if ($$unit > 0 || empty($return) === false) {            
                $return .= ' '.$$unit.$unit;
            }
        }
        return trim($return);
    }
}
