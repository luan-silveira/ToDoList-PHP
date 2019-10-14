<?php

class QueryBuilder {

	private $select = '*';
	private $from;
	private $where;
	private $parameters;
	
	
	public function __construct(){

	}

	public function select(...$fields){
		foreach ($fields as $field){
			$this->select .= (empty($this->select) ? '' : ',') . "`$field`";
		}
		return $this;
	}

	public function from($table){
		$this->from = "`$table`";
		return $this;
	}

	public function where(){
		$this->whereStatement(func_get_args());
		return $this;
	}

	public function orWhere(){
		$this->whereStatement(func_get_args(), 'OR');
		return $this;
	}

	private function whereStatement($args, $logicOper = 'AND'){
		
		if ($numArgs > 3 || $numArgs < 2) {
			throw new Exception('A função where() requer 2 ou 3 argumentos');
		}

		$args = getArgsWhere(func_get_args());
		if (!$where){
			$where = 'WHERE ';
		} else {
			$where .= " $logicOper ";
		}
		$where .= "{$args->field} {$args->oper} '$args->value'";

		return $this;
	}

	private function getArgsWhere($args){
		$ret = new stdClass;
		$ret->field = $args[0];
		$ret->oper = '=';

		if ($numArgs = 2){
			$ret->val = $args[1];
		} else {
			$ret->oper = $args[1];
			$ret->val = $args[2];
		}

		return $ret;
	}

	public function build(){
		return "SELECT $select FROM $from WHERE $where";
	}
}