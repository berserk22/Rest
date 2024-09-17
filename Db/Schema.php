<?php

/**
 * @author Sergey Tevs
 * @email sergey@tevs.org
 */

namespace Modules\Rest\Db;

use DI\DependencyException;
use DI\NotFoundException;
use Modules\Database\Migration;
use Illuminate\Database\Schema\Blueprint;

class Schema extends Migration{

    /**
     * @return void
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function create(): void {
        if (!$this->schema()->hasTable('api_token')) {
            $this->schema()->create('api_token', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->integer('api_user_id');
                $table->string('access_token');
                $table->string('refresh_token');
                $table->string('scope');
                $table->string('token_type');
                $table->integer('expires_in');
                $table->integer('active')->default(0);
                $table->dateTime('created_at');
                $table->dateTime('updated_at');
                $table->index('active');
            });
        }

        if (!$this->schema()->hasTable('api_user')) {
            $this->schema()->create('api_user', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->string('username');
                $table->string('password');
                $table->string('client_id');
                $table->string('client_secret');
                $table->integer('active')->default(0);
                $table->dateTime('created_at');
                $table->dateTime('updated_at');
                $table->index('active');
            });
        }
    }

    /**
     * @return void
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function delete(): void {
        if (!$this->schema()->hasTable('api_token')) {
            $this->schema()->drop('api_token');
        }

        if (!$this->schema()->hasTable('api_user')) {
            $this->schema()->drop('api_user');
        }
    }
}
