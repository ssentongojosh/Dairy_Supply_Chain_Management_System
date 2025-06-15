public function up()
{
    Schema::create('chat_messages', function (Blueprint $table) {
        $table->id();
        $table->string('sender_role');
        $table->string('receiver_role');
        $table->text('message');
        $table->timestamps();
    });
}

