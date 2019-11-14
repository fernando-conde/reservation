<?php
namespace DbSync;

abstract class DataBase
{
    /**
     * @param string
     * @return array
     */
    public function get($query, $filter = null)
    {
        $return = [];
        if (isset($this->connection) === true) {
            $parameters = null;
            if (isset($filter) === true) {
                $filter = trim($filter);
                str_replace('  ', ' ', $filter);
                str_replace(' =', '=', $filter);
                str_replace('= ', '=', $filter);

                $list = explode(' ', $filter);
                foreach ($list as $key => $sub) {
                    if (strpos('=', $sub) >= 0) {                        
                        $list2 = explode('=', $sub);
                        $parameter = $list2[0];
                        if (substr($parameter, 0, 1) === '@') {
                            $parameter = substr($parameter, 1);
                        }
                        $parameter = ':'.$parameter;

                        $parameters[$parameter] = $list2[1];
                        $list2[1] = $parameter;
                        $list[$key] = implode('=', $list2);
                    }
                }
                
                $filter = implode(' ', $list);

                $list = explode(' ', $query);
                if ($list[0] === 'SELECT') {
                    $query .= ' WHERE';
                } 

                $query .= ' '.$filter;
            }

            $request = $this->connection->prepare($query);
            $request->execute($parameters);
            $data = $request->fetchAll(\PDO::FETCH_ASSOC);

            if (is_array($data) === true) {
                $return = $data;
            }
        }
        return $return;
    }
}
