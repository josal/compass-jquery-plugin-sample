class Player < ActiveRecord::Base  
  gridify :example01,
    :title          => "Football players",
    :url            => "/jqgrid/players",
    :only           => [:id, :pseudo, :firstname, :lastname, :email, :role],
    :width_fit      => :scroll,
    :search_button  => true,  
    :refresh_button => true,
    :pager          => true
    
  gridify :example02,
    :url   => "/jqgrid/players",
    :pager => true
    
  gridify :example03,
    :url   => "/jqgrid/players",
    :pager => true
    
  gridify :example04,
    :url   => "/jqgrid/players",
    :pager => true
    
  gridify :example05,
    :url   => "/jqgrid/players",
    :pager => true
end
