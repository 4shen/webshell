
function command_handler(p, r)
{
    /*var rpc =     {
                        category : 'machine | job | other'
                        name : 'func_name',
                        args: [],
                    };
   */
    this.parser = p;
  
    this.print_render = r;

    this.result_callback = null;
  
    this.error_callback  = null;

    this.run = function(rpc)
    {
        var h_rpc = null;
        
        if ( !rpc )
            return;  //help information

       

        if (rpc.category == 'machine')
        {
            h_rpc = new $.JsonRpcClient({ajaxUrl: '/hamsta/rpc_machine.php'});
            h_rpc.call(
                rpc.name, rpc.args,
                get_result_callback(rpc.name),
                get_error_callback(rpc.name)
        );
        }
        else if (rpc.category == 'job')
        {
            h_rpc = new $.JsonRpcClient({ajaxUrl: 'job_rpc.php'});
        }
        else {}
        
    }
    
    function get_result_callback(func_name)
    {
         var func = null;
         if (func_name == 'machine_list' || func_name == 'machine_search')
             func = machine_list_result_callback;
         else if (func_name == 'machine_get')
             func = machine_get_result_callback;
         else
         {}
         return func;
    }

    function get_error_callback(func_name)
    {
        return function(error){};
    }


    function machine_list_result_callback( result )
    {
        var data = result;
        if (data.length > 0)
        {
            print_machine_list(data);
        }
         
    }
   
    function machine_get_result_callback( result )
    {
        var m = result;
        if (m)
        {
            print_machine_property(m);
        }
         
    }
    function print_machine_list(data)
    {
        var columns = [ {disname:'Id', dbname:'machine_id'}, {disname:'Hostname', dbname:'name'}, 
                        {disname:'Status', dbname:'machine_status'}, {disname:'Usage', dbname:'usage'}, 
                        {disname:'Reservation', dbname:'usage'}, {disname:'Product', dbname:'product'}, 
                        {disname:'CPU_arch', dbname:'arch'}, {disname:'Kernel', dbname:'kernel'}, 
                      ];
        this.r.print_table(columns, data);

    }

    function print_machine_property(m)
    {
        var keys = [ {disname:'Id', dbname:'machine_id'}, {disname:'Hostname', dbname:'name'}, 
                     {disname:'Status', dbname:'machine_status'}, {disname:'Usage', dbname:'usage'}, 
                     {disname:'Reservation', dbname:'reservation'}, {disname:'Product', dbname:'product'}, 
                     {disname:'CPU_arch', dbname:'arch'}, {disname:'Kernel', dbname:'kernel'}, 
                   ];
        this.r.print_property(keys, m, 2);
    }
}
