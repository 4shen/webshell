<?php

class job
{

  public function __construct($job=null)
  {
    
    #connect to database
    $this->db = new mysql_pdo();

  }
  
  public function get_job_byid($job_id)
  {
  
    $this->db->pre('SELECT job_id,job_status.job_status,machine_id,machine.name
                   ,job.short_name,start,stop,job_on_machine_id FROM job_on_machine
                   LEFT JOIN job_status USING(job_status_id)
                   LEFT JOIN job USING(job_id)
                   LEFT JOIN machine USING(machine_id) 
                   WHERE job_id=:job_id
                   ');
    $this->db->bind(':job_id',$job_id);
    $job=$this->db->exe();
    return $job[0];
  
  }

  public function job_list()
  {

    $this->db->pre('SELECT job_id,job_status.job_status,machine.name
                   ,job.short_name,start,stop,machine_id FROM job_on_machine
                   LEFT JOIN job_status USING(job_status_id)
                   LEFT JOIN job USING(job_id)
                   LEFT JOIN machine USING(machine_id) 
                   ORDER BY job_id DESC 
                   LIMIT 0,15');
    return $this->db->exe();
  }

  public function job_get($job_id)
  {
    $job=$this->get_job_byid($job_id);  
    $this->db->pre('SELECT * FROM log WHERE machine_id = :machine_id AND job_on_machine_id = :job_on_machine_id ORDER BY log_id ASC');
    $this->db->bind(':machine_id',$job['machine_id']);
    $this->db->bind(':job_on_machine_id',$job['job_on_machine_id']);
    $all_log = $this->db->exe();
    $job[]=$all_log;
    return $job;

  }

}


?>

