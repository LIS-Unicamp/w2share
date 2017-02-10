<?php
namespace AppBundle\Model;

/**
 * Description of Search
 *
 * @author lucas
 */
class Search
{
    private $driver;
        
    public function __construct($driver)
    {
        $this->driver = $driver;
    }            
    
    public function concepts()
    {
        $query = "
            select distinct ?concept ?label where { GRAPH <".$this->driver->getDefaultGraph()."> {
            {
                [] a ?concept.
                OPTIONAL { ?concept skos:prefLabel ?label. }
                } union {
                                ?subject ?concept ?object.
                                OPTIONAL { ?concept skos:prefLabel ?label. }
                }
            }}
        ";
                        
        return $this->driver->getResults($query);
    }
    
    public function query($query, $concept)
    {
        $query2 = "
            SELECT DISTINCT * WHERE {GRAPH <".$this->driver->getDefaultGraph()."> {
                {
                    ?title a <".$concept.">
                    FILTER regex(?title, \"".$query."\", \"i\" )
                } union {
                    ?subject <".$concept."> ?title 
                    FILTER regex(?title, \"".$query."\", \"i\" )
                }
            }}
            ";

        return $this->driver->getResults($query2);
    }        
}
