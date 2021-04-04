<template>
    <div>
        <!-- 新規追加フォーム -->
        <div>
            <label for="title">タイトル：</label>
            <input type="text" name="title" id="title" v-model="title" />
            <br>
            <label for="author">著者名　：</label>
            <input type="text" name="author" id="author" v-model="author" />
            <br>
            <label for="comment">コメント：</label>
            <br>
            <textarea name="comment" id="comment" cols="30" rows="4" v-model="comment"></textarea>
            <br>
            <input type="file" name="image" id="image" v-on:change="confirmImage" v-if="view">
            <br>
            <p v-if="confirmImage">
                <img id="img_a" class="img" v-bind:src="confirmedImage" alt="image" />
            </p>
            <p>{{ confirm_message }}</p>
            <br>
            <button v-on:click="addBook">登録</button>
            <hr>
        </div>

        <!-- Bookの一覧画面 -->
        <div>
            <table>
                <tr>
                    <th>
                        ID
                    </th>
                    <th>
                        タイトル
                    </th>
                    <th>
                        著者名
                    </th>
                    <th>
                        コメント（評価）
                    </th>
                    <th>
                        イメージ
                    </th>
                    <th>-</th>
                    <th>-</th>
                </tr>
                <tr v-for="book in books" v-bind:key="book.id">
                    <td>
                        {{ book.id }}
                    </td>
                    <td>
                        {{ book.title }}
                    </td>
                    <td>
                        {{ book.author }}
                    </td>
                    <td>
                        {{ book.comment }}
                    </td>
                    <td>
                        <a v-bind:href="book.path" target="_blank" rel="noopener noreferrer">
                        <!-- <img class="img" v-bind:src="`${book.path}`" alt="image" /> -->
                        <img class="img" v-bind:src="book.path" alt="image" />
                        </a>
                    </td>
                    <td>
                        <button v-bind:disabled="isPush" v-on:click="displayUpdate(book.id, book.title, book.author, book.comment)">
                            編集
                        </button>
                    </td>
                    <td>
                        <button v-bind:disabled="isPush" v-on:click="deleteBook(book.id)">
                            削除
                        </button>
                    </td>
                </tr>
            </table>
            <hr>
        </div>

        <!-- 編集フォーム -->
        <div v-if="updateForm">
            <p>id:{{ updateId }}, {{ updateTitle }}の情報を編集します</p>
            <label for="updateTitle">タイトル：</label>
            <input type="text" name="updateTitle" id="updateTitle" v-model="updateTitle">
            <br>
            <label for="updateAuthor">著者名　：</label>
            <input type="text" name="updateAuthor" id="updateAuthor" v-model="updateAuthor">
            <br>
            <label for="updateComment">コメント：</label>
            <br>
            <textarea name="updateComment" id="updateComment" cols="20" rows="10" v-model="updateComment"></textarea>
            <br>
            <button v-on:click="updateBook(updateId, updateTitle, updateAuthor, updateComment)">更新</button>
            <button v-on:click="updateCancel">キャンセル</button>
            <hr>
        </div>

        <!-- エラーメッセージ -->
        <div v-if="message">
            <p>エラーが発生しています</p>
            <p>{{ message }}</p>
        </div>
    </div>
</template>

<script>
    export default {
        data: function() {
            return{
                message: "",
                confirm_message: "",
                isPush: false,
                updateForm: false,
                books: {},
                title: "",
                author: "",
                comment: "",
                updateId: "",
                updateTitle: "",
                updateAuthor: "",
                updateComment: "",
                file: "",
                view: true,
                confirmedImage: ""
            };
        },
        created: function() {
            this.getBooks();
        },
        methods: {
            getBooks() {
                axios
                .get('/api/books')
                .then(response => {
                    this.books = response.data;
                    console.log(response.data);
                })
                .catch(error => {
                    this.message = error;
                });
            },
            addBook() {
                let data = new FormData();
                data.append('file', this.file);
                data.append('title', this.title);
                data.append('author', this.author);
                data.append('comment', this.comment);

                axios                
                .post('/api/books', data)
                .then(response => {
                    this.getBooks();
                    this.file = "";
                    this.title = "";
                    this.author = "";
                    this.comment = "";
                    this.message = "";
                    this.confirmedImage = "";
                    //ファイル選択のクリア
                    this.view = false;
                    this.$nextTick(function() {
                        this.view = true;
                    });
                    console.log(response);
                })
                .catch(error => {
                    this.message = error;
                });
            },
            displayUpdate(id, title, author, comment) {
                this.isPush = true;
                this.updateForm = true;
                this.message = "";
                this.updateId = id;
                this.updateTitle = title;
                this.updateAuthor = author;
                this.updateComment = comment;
            },
            updateCancel() {
                this.isPush = false;
                this.updateForm = false;
                this.message = "";
            },
            updateBook(updateId, updateTitle, updateAuthor, updateComment) {
                axios
                .put('/api/books/' + updateId, {
                    title: this.updateTitle,
                    author: this.updateAuthor,
                    comment: this.updateComment
                })
                .then(response => {
                    this.getBooks();
                    this.isPush = false;
                    this.updateForm = false;
                    this.message = "";
                    console.log(response);
                })
                .catch(error => {
                    this.message = error;
                });
            },
            deleteBook(id, title) {
                axios
                .delete('/api/books/' + id)
                .then(response => {
                    alert("ID「" + id + "」の情報を削除しました");
                    this.getBooks();
                    this.message = "";
                    console.log(response);
                })
                .catch(error => {
                    this.message = error;
                });
            },
            confirmImage(event){
                this.confirm_message = "";
                this.file = event.target.files[0];
                if(!this.file.type.match('image.*')) {
                    this.confirm_message = '画像ファイルを選択してください';
                    this.confirmedImage = "";
                    return;
                };
                this.createImage(this.file);
            },
            createImage(file) {
                let reader = new FileReader();
                reader.readAsDataURL(file);
                reader.onload = event => {
                    this.confirmedImage = event.target.result;
                };
            },
        },
    };
</script>

<style scoped>
table{
  width: 70%;
  border-collapse:separate;
  border-spacing: 0;
}

table th:first-child{
  border-radius: 5px 0 0 0;
}

table th:last-child{
  border-radius: 0 5px 0 0;
  border-right: 1px solid #3c6690;
}

table th{
  text-align: center;
  color:white;
  background: linear-gradient(#829ebc,#225588);
  border-left: 1px solid #3c6690;
  border-top: 1px solid #3c6690;
  border-bottom: 1px solid #3c6690;
  box-shadow: 0px 1px 1px rgba(255,255,255,0.3) inset;
  /* width: 25%; */
  padding: 10px 0;
}

table td{
  text-align: center;
  border-left: 1px solid #a8b7c5;
  border-bottom: 1px solid #a8b7c5;
  border-top:none;
  box-shadow: 0px -3px 5px 1px #eee inset;
  /* width: 25%; */
  padding: 10px 0;
}

table td:last-child{
  border-right: 1px solid #a8b7c5;
}

table tr:last-child td:first-child {
  border-radius: 0 0 0 5px;
}

table tr:last-child td:last-child {
  border-radius: 0 0 5px 0;
}

.img {
    width: 100px;
}
</style>
