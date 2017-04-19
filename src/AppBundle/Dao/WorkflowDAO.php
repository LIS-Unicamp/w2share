<?php
namespace AppBundle\Dao;

/**
 * Description of the Research Object model
 *
 * @author lucas
 */
class WorkflowDAO
{
    private $driver;
        
    private $container;
    
    public function __construct($driver, \Symfony\Component\DependencyInjection\ContainerInterface $container)
    {
        $this->container = $container;
        $this->driver = $driver;
    }  
    
    public function findWorkflowInput($input_uri)
    {
        // inputs information
        $query = "
            SELECT * WHERE {GRAPH <".$this->driver->getDefaultGraph()."> {
                ?workflow a wfdesc:Workflow;
                wfdesc:hasInput <".$input_uri.">.
                OPTIONAL { <".$input_uri."> biocat:exampleData ?exampleData. }
                OPTIONAL { <".$input_uri."> rdfs:label ?label. }
                OPTIONAL { <".$input_uri."> dcterms:description ?description. }
            }}
            ";
        
        $input_array = $this->driver->getResults($query);   
        
        if (count($input_array) > 0)
        {
            $input = new \AppBundle\Entity\Input();
            $input->setUri($input_uri);
            if (in_array('description', array_keys($input_array[0])))
            {
                $input->setDescription($input_array[0]['description']['value']);
            }
            if (in_array('exampleData', array_keys($input_array[0])))
            {
                $input->setExampleData($input_array[0]['exampleData']['value']);
            }
            $input->setLabel($input_array[0]['label']['value']); 
            
            $workflow = new \AppBundle\Entity\Workflow();
            $workflow->setUri($input_array[0]['workflow']['value']);            
            $input->setWorkflow($workflow);
            
            return $input;  
        }
        
        return null;
    }
    
    public function findProcessInput($input_uri)
    {
        $query = "
            SELECT DISTINCT * WHERE {GRAPH <".$this->driver->getDefaultGraph()."> {
                ?process a wfdesc:Process;
                wfdesc:hasInput <".$input_uri.">.
                OPTIONAL { <".$input_uri."> biocat:exampleData ?exampleData. }
                OPTIONAL { <".$input_uri."> rdfs:label ?label }
                OPTIONAL { <".$input_uri."> dcterms:description ?description }
            }}
            ";
        
        $inputs = $this->driver->getResults($query);   
        
        if (count($inputs) > 0)
        {
            $input = new \AppBundle\Entity\Input();
            $input->setUri($input_uri);
            if (in_array('description', array_keys($inputs[0])))
            {
                $input->setDescription($inputs[0]['description']['value']);
            }
            if (in_array('exampleData', array_keys($inputs[0])))
            {
                $input->setExampleData($inputs[0]['exampleData']['value']);
            }
            $input->setLabel($inputs[0]['label']['value']);  
            
            $process = new \AppBundle\Entity\Process();
            $process->setUri($inputs[0]['process']['value']);            
            $input->setProcess($process);
            
            return $input;  
        }
        
        return null;
    }
    
    public function findWorkflowOutput($output_uri)
    {
        // outputs information
        $query = "
            SELECT * WHERE 
            {
                GRAPH <".$this->driver->getDefaultGraph()."> 
                {
                    ?workflow a wfdesc:Workflow;
                    wfdesc:hasOutput <".$output_uri.">.
                    OPTIONAL { <".$output_uri."> biocat:exampleData ?exampleData. }
                    OPTIONAL { <".$output_uri."> rdfs:label ?label. }
                    OPTIONAL { <".$output_uri."> dcterms:description ?description. }
                }
            }
            ";
        
        $outputs = $this->driver->getResults($query); 
        
        if (count($outputs) > 0)
        {
            $output = new \AppBundle\Entity\Output();
            $output->setUri($output_uri);
            if (in_array('description', array_keys($outputs[0])))
            {
                $output->setDescription($outputs[0]['description']['value']);
            }
            if (in_array('exampleData', array_keys($outputs[0])))
            {
                $output->setExampleData($outputs[0]['exampleData']['value']);
            }
            $output->setLabel($outputs[0]['label']['value']);     
            
            $workflow = new \AppBundle\Entity\Workflow();
            $workflow->setUri($outputs[0]['workflow']['value']);            
            $output->setWorkflow($workflow);
            
            return $output;  
        }
        
        return null;
    }
    
    public function findProcessOutput($output_uri)
    {
        // outputs information
        $query = "
            SELECT DISTINCT * WHERE {GRAPH <".$this->driver->getDefaultGraph()."> {
                ?process a wfdesc:Process;
                wfdesc:hasOutput <".$output_uri.">.
                OPTIONAL { <".$output_uri."> biocat:exampleData ?exampleData. }
                OPTIONAL { <".$output_uri."> rdfs:label ?label }
                OPTIONAL { <".$output_uri."> dcterms:description ?description }
            }}
            ";
        
        $outputs = $this->driver->getResults($query);   
        
        if (count($outputs) > 0)
        {
            $output = new \AppBundle\Entity\Output();
            $output->setUri($output_uri);
            if (in_array('description', array_keys($outputs[0])))
            {
                $output->setDescription($outputs[0]['description']['value']);
            }
            if (in_array('exampleData', array_keys($outputs[0])))
            {
                $output->setExampleData($outputs[0]['exampleData']['value']);
            }
            $output->setLabel($outputs[0]['label']['value']);   
            
            $process = new \AppBundle\Entity\Process();
            $process->setUri($outputs[0]['process']['value']);            
            $output->setProcess($process);
            
            return $output;  
        }
        
        return null;
    }
    
    public function updateInput(\AppBundle\Entity\Input $input)
    {        
        $query = 
        "MODIFY <".$this->driver->getDefaultGraph().">
        DELETE
        {
            <".$input->getUri()."> biocat:exampleData ?exampleData.
            <".$input->getUri()."> dcterms:description ?description.         
        }
        INSERT
        {
            <".$input->getUri()."> biocat:exampleData '".$input->getExampleData()."'.
            <".$input->getUri()."> dcterms:description '".$input->getDescription()."'.          
        }
        WHERE
        {
            <".$input->getUri()."> a wfdesc:Input.
            OPTIONAL { <".$input->getUri()."> biocat:exampleData ?exampleData. }
            OPTIONAL { <".$input->getUri()."> dcterms:description ?description. }    
        }";
       
        $this->driver->getResults($query);
    } 
    
    public function updateOutput(\AppBundle\Entity\Output $output)
    {        
        $query = 
        "MODIFY <".$this->driver->getDefaultGraph().">
        DELETE
        {
            <".$output->getUri()."> biocat:exampleData ?exampleData.
            <".$output->getUri()."> dcterms:description ?description.         
        }
        INSERT
        {
            <".$output->getUri()."> biocat:exampleData '".$output->getExampleData()."'.
            <".$output->getUri()."> dcterms:description '".$output->getDescription()."'.          
        }
        WHERE
        {
            <".$output->getUri()."> a wfdesc:Output.
            OPTIONAL { <".$output->getUri()."> biocat:exampleData ?exampleData. }
            OPTIONAL { <".$output->getUri()."> dcterms:description ?description. }    
        }";
       
        $this->driver->getResults($query);
    } 
    
    public function updateWorkflow(\AppBundle\Entity\Workflow $workflow)
    {        
        $query = 
        "MODIFY <".$this->driver->getDefaultGraph().">
        DELETE
        {
            <".$workflow->getUri()."> dcterms:title ?title.
            <".$workflow->getUri()."> dcterms:description ?description.         
        }
        INSERT
        {
            <".$workflow->getUri()."> dcterms:title '".$workflow->getTitle()."'.
            <".$workflow->getUri()."> dcterms:description '".$workflow->getDescription()."'.          
        }
        WHERE
        {
            <".$workflow->getUri()."> a wfdesc:Workflow.
            OPTIONAL { <".$workflow->getUri()."> dcterms:title ?title. }
            OPTIONAL { <".$workflow->getUri()."> dcterms:description ?description. }    
        }";
       
        $this->driver->getResults($query);
    }     
    
    public function updateProcess(\AppBundle\Entity\Process $process)
    {        
        $query = 
        "MODIFY <".$this->driver->getDefaultGraph().">
        DELETE
        {
            <".$process->getUri()."> dcterms:description ?description.         
        }
        INSERT
        {
            <".$process->getUri()."> dcterms:description '".$process->getDescription()."'.          
        }
        WHERE
        {
            <".$process->getUri()."> a wfdesc:Process.
            OPTIONAL { <".$process->getUri()."> dcterms:description ?description. }    
        }";
       
        $this->driver->getResults($query);
    }     
}