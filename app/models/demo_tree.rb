class DemoTree < ActiveRecord::Base 
  acts_as_tree_on_steroids :family_level => 1
  set_table_name "demo_trees"
  
  def self.get_children(id)
    result = Array.new
    if id == "1"
      node = find_by_title("ROOT")
    else
      node = find(id)
    end
    children = node.children
    if id != 0
      children.each do |child| 
        result << {
          :attr  => {:id => "node_#{child.id.to_s}", :rel => child.ntype},
          :data  => child.title,
          :state => (child.right - child.left) > 1 ? "closed" : ""
        }
      end
    end    
    return result
  end
  
  def self.search(search_str)    
    result = Array.new
    nodes = find(:all, :conditions => "title LIKE '%#{search_str}%'")  
    if nodes
      nodes.each do |node|
        result << "#node_#{node.id.to_s}"
      end
    end       
    return result
  end
  
  def self.create_node(params)
    if params[:id].to_i > 0
      parent = find(params[:id])
    else
      parent = find_by_title("ROOT")
    end
    node = new()
    node.parent_id = parent.id
    node.position  = params[:position]
    node.left      = parent[:right]
    node.right     = node.left + 1
    node.level     = parent.level + 1
    node.title     = params[:title]
    node.ntype     = params[:type]
    if node.save
      node.ancestors.each do |ancestor|
        ancestor.right += 2
        ancestor.save
      end
      update_all("left = left + 2, right = right + 2", "left >= #{node.right}")
      result = { :status => 1, :id => node.id }   
    else
      result = { :status => 0 }   
    end         
    return result
  end
  
  def self.remove_node(id)
    node = find(id)
    left = node.left
    right = node.right
    dif = right - left + 1
    pid = node.parent_id
    pos = node.position
    
    #  deleting node and its children
    node.delete_branch
    # shift left indexes of nodes right of the node
    update_all("left = left - #{dif}", "left > #{right}")
    # shift right indexes of nodes right of the node and the node's parents
    update_all("right = right - #{dif}", "right > #{left}")  
    # Update position of siblings below the deleted node
    update_all("position = position -1", "parent_id = #{pid} AND position > #{pos}")    
    result = { :status => 1 }         
    return result
  end
  
  def self.rename_node(params)
    node = find(params[:id])
    node.title = params[:title]
    if node.save
      return { :status => 1 }   
    end    
  end
  
  def self.copy_children(id, node)
    node.children.each do |child|
      result = copy_node(id, child)
      copy_children(result[:id], child)
    end
  end

  def self.copy_node(id, node)
    params = {} 
    params[:id]       = id
    params[:position] = (node.position) ? node.position : 0 
    params[:title]    = node.title  
    params[:type]     = node.ntype    
    result = create_node(params) 
    return result  
  end
  
  def self.move_node(params)
    node_old = find(params[:id])
    result   = copy_node(params[:ref], node_old)
    copy_children(result[:id], node_old)
    if params[:copy] == "1"   
      result = { :status => 1, :id => result[:id] }
    else
      remove_node(params[:id])
      result = { :status => 1, :id => "1" }
    end
    return result
  end
  
  def self.analyze
    report = []
    
    # Analyze root
    nodes = find_all_by_title("ROOT")    
    case nodes.size
      when 0 then
        report << "<div class='span-4'>[FAIL]</div><div class='span-20 last'>No root node.</div>"
      when 1 then
        report << "<div class='span-4'>[OK]</div><div class='span-20 last'>Just one root node.</div>"
        if nodes[0].left == 1
          report << "<div class='span-4'>[OK]</div><div class='span-20 last'>Root node's left index is 1.</div>"
        else
          report << "<div class='span-4'>[FAIL]</div><div class='span-20 last'>Root node's left index is not 1.</div>"
        end
      else
        report << "<div class='span-4'>[FAIL]</div><div class='span-20 last'>More than one root node.</div>"
    end
        
    # Missing parents (Unnecessary)
    
    #b = count(:conditions => "parent_id != 0")
    
    #c = count(:joins => "INNER JOIN demo_trees a ON a.id = demo_trees.parent_id")


#    select cust.id, count(*)
#    from order
#    inner join order on order.cust_id = cust.id
#    group by cust.id
#    having count(*) > 10


    
#    $this->db->query("" . 
#      "SELECT " . 
#        "COUNT(*) FROM `".$this->table."` s " . 
#      "WHERE " . 
#        "`".$this->fields["parent_id"]."` != 0 AND " . 
#        "(SELECT COUNT(*) FROM `".$this->table."` WHERE `".$this->fields["id"]."` = s.`".$this->fields["parent_id"]."`) = 0 ");
#    $this->db->nextr();
#    $report[] = ($this->db->f(0) > 0) ? "[FAIL]\tMissing parents." : "[OK]\tNo missing parents.";


    # Right index
    max     = maximum(:right)
    entries = count

    if max/2 == entries
      report << "<div class='span-4'>[OK]</div><div class='span-20 last'>Right index matches node count.</div>"
    else
      report << "<div class='span-4'>[FAIL]</div><div class='span-20 last'>Right index does not match node count.</div>"
    end 

    # Adjacency
    
    #count1 = count(:conditions => right)

#
#    $this->db->query("" . 
#      "SELECT COUNT(`".$this->fields["id"]."`) FROM `".$this->table."` s " . 
#      "WHERE " . 
#        "(SELECT COUNT(*) FROM `".$this->table."` WHERE " . 
#          "`".$this->fields["right"]."` < s.`".$this->fields["right"]."` AND " . 
#          "`".$this->fields["left"]."` > s.`".$this->fields["left"]."` AND " . 
#          "`".$this->fields["level"]."` = s.`".$this->fields["level"]."` + 1" . 
#        ") != " .
#        "(SELECT COUNT(*) FROM `".$this->table."` WHERE " . 
#          "`".$this->fields["parent_id"]."` = s.`".$this->fields["id"]."`" . 
#        ") "
#      );
#    $this->db->nextr();
#    $report[] = ($this->db->f(0) > 0) ? "[FAIL]\tAdjacency and nested set do not match." : "[OK]\tNS and AJ match";
#

    if max/2 == entries
      report << "<div class='span-4'>[OK]</div><div class='span-20 last'>Adjacency and nested set do match.</div>"
    else
      report << "<div class='span-4'>[FAIL]</div><div class='span-20 last'>Adjacency and nested set do not match.</div>"
    end

    return report * "<br/>"
  end
  
  def self.rebuild_demo
    ActiveRecord::Migration.drop_table :demo_trees
    ActiveRecord::Migration.create_table :demo_trees do |t|
      t.column :parent_id,            :integer, :limit => 2, :null => true
      t.column :id_path,              :string, :limit => 200, :null => true
      t.column :level,                :integer, :limit => 1, :null => true
      t.column :children_count,       :integer, :limit => 2, :null => true

      #optional
      t.column :family_id,            :integer, :limit => 2, :null => true
    
      t.column :position,             :integer, :null => false
      t.column :left,                 :integer, :null => false
      t.column :right,                :integer, :null => false
      t.column :level,                :integer, :null => false
      t.column :title,                :text
      t.column :ntype,                :text,    :null => true    
    end

    ActiveRecord::Migration.add_index :demo_trees, :parent_id
    ActiveRecord::Migration.add_index :demo_trees, :id_path
    
    root = create(:parent_id => 0,  :position => 0, :left => 1,  :right => 14, :level => 0, :title => 'ROOT')
    create(:parent_id => root.id,   :position => 0, :left => 2,  :right => 11, :level => 1, :title => 'C:',         :ntype => 'drive')
    create(:parent_id => root.id+1, :position => 0, :left => 3,  :right => 6,  :level => 2, :title => '_demo',     :ntype => 'folder')
    create(:parent_id => root.id+2, :position => 0, :left => 4,  :right => 5,  :level => 3, :title => 'index.html', :ntype => 'default')
    create(:parent_id => root.id+1, :position => 1, :left => 7,  :right => 10, :level => 2, :title => '_docs',      :ntype => 'folder')
    create(:parent_id => root.id,   :position => 1, :left => 12, :right => 13, :level => 1, :title => 'D:',         :ntype => 'drive')
    create(:parent_id => root.id+4, :position => 0, :left => 8,  :right => 9,  :level => 3, :title => 'zmei.html',  :ntype => 'default')
    return { :status => 0 }   
  end 
  
end
