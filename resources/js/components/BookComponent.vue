<template>
    <div>
        <!-- 新規追加フォーム -->
        <div class="modal-wrapper" v-if="inputForm">
            <div class="modal-overlay" v-on:click="inputCancel"></div>
            <div class="modal-window">
                <div class="modal-content">
                    <label for="title">タイトル：</label>
                    <input type="text" name="title" id="title" v-model="title" />
                    <br>
                    <label for="author">著者名　：</label>
                    <input type="text" name="author" id="author" v-model="author" />
                    <br>
                    <label for="comment">コメント：</label>
                    <br>
                    <textarea name="comment" id="comment" cols="30" rows="3" v-model="comment"></textarea>
                    <br>
                    <input type="file" name="image" id="image" v-on:change="confirmImage" v-if="view">
                    <br>
                    <p v-if="confirmImage">
                        <img class="img" v-bind:src="confirmedImage" alt="" />
                    </p>
                    <p>{{ confirm_message }}</p>
                    <br>
                    <button v-on:click="addBook">登録</button>
                    <button v-on:click="inputCancel">キャンセル</button>
                </div>
                <button id="x-button" class="modal-close" v-on:click="inputCancel">×</button>
            </div>
        </div>

        <!-- Bookの一覧画面 -->
        <div>
            <label for="serch_title">タイトル検索：</label>
            <input type="search" name="" id="" v-model="search_title">
            <button v-on:click="searchBooks">検索</button>
            <button v-on:click="clearBooks">クリア</button>
            <input type="button" value="新規登録" v-on:click="inputStart">
            <br>
            <p v-if="search_error_message"> {{ search_error_message }} </p>
            <table>
                <tr v-if="viewtr">
                    <th class="th_id">
                        ID
                    </th>
                    <th class="th_title">
                        タイトル
                    </th>
                    <th class="th_author">
                        著者名
                    </th>
                    <th class="th_commet">
                        コメント（評価）
                    </th>
                    <th class="th_image">
                        イメージ
                    </th>
                    <th class="th_edit">-</th>
                    <th class="th_delete">-</th>
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
                            <img class="img" v-bind:src="book.path" alt="【動画】" />
                            <!-- <video controls width="150px">
                                <source class="video" type="" v-bind:src="book.path" poster="none">
                                *** type="video/mp4" type="video/webm" type="video/wmv" ***
                            </video> -->
                        </a>
                    </td>
                    <td>
                        <button v-bind:disabled="isPush"
                            v-on:click="displayUpdate(book.id, book.title, book.author, book.comment)">
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
        <div class="modal-wrapper" v-if="updateForm">
            <div class="modal-overlay" v-on:click="updateCancel"></div>
            <div class="modal-window">
                <div class="modal-content">
                    <h4>ID:{{ updateId }}, {{ updateTitle }}の情報を編集します</h4>
                    <label for="updateTitle">タイトル：</label>
                    <input type="text" name="updateTitle" id="updateTitle" v-model="updateTitle">
                    <br>
                    <label for="updateAuthor">著者名　：</label>
                    <input type="text" name="updateAuthor" id="updateAuthor" v-model="updateAuthor">
                    <br>
                    <label for="updateComment">コメント：</label>
                    <br>
                    <textarea name="updateComment" id="updateComment" cols="30" rows="3"
                        v-model="updateComment"></textarea>
                    <br>
                    <button v-on:click="updateBook(updateId, updateTitle, updateAuthor, updateComment)">更新</button>
                    <button v-on:click="updateCancel">キャンセル</button>
                </div>
                <button id="x-button" class="modal-close" v-on:click="updateCancel">×</button>
            </div>
        </div>

        <!-- エラーメッセージ -->
        <div v-if="message">
            <p>エラーが発生しています</p>
            <p>{{ message }}</p>
        </div>
    </div>
</template>

<script>
    import axios from './axios-auth.js';
    export default {
        data: function () {
            return {
                message: "",
                confirm_message: "",
                search_error_message: false,
                isPush: false,
                inputForm: false,
                updateForm: false,
                books: [],
                title: "",
                author: "",
                comment: "",
                search_title: "",
                updateId: "",
                updateTitle: "",
                updateAuthor: "",
                updateComment: "",
                file: "",
                view: true,
                viewtr: false,
                confirmedImage: ""
            };
        },
        created: function () {
            this.getBooks();
        },
        methods: {
            getBooks() {
                this.search_error_message = "";
                axios
                    .get('/api/books')
                    .then(response => {
                        this.books = response.data;
                        if (this.books.length > 0) {
                            this.viewtr = true;
                        } else {
                            this.viewtr = false;
                        };
                        console.log(response.data);
                    })
                    .catch(error => {
                        this.message = error;
                    });
            },
            searchBooks() {
                this.search_error_message = "";
                if (this.search_title == "") {
                    this.search_error_message = '検索文字列を入力してください';
                    return;
                };
                axios
                    .get('/api/books/search/' + this.search_title)
                    .then(response => {
                        this.books = response.data;
                        console.log(response.data);
                    })
                    .catch(error => {
                        this.message = error;
                    });
            },
            clearBooks() {
                this.getBooks();
                this.search_title = "";
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
                        this.search_title = "";
                        this.confirmedImage = "";
                        //ファイル選択のクリア
                        this.view = false;
                        this.$nextTick(function () {
                            this.view = true;
                        });
                        console.log(response.data);
                    })
                    .catch(error => {
                        this.message = error;
                    });
                    this.inputCancel();
            },
            inputStart() {
                this.message = "";
                this.inputForm = true;
            },
            inputCancel() {
                this.inputForm = false;
                this.message = "";
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
                        this.search_title = "";
                        console.log(response.data);
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
                        console.log(response.data);
                    })
                    .catch(error => {
                        this.message = error;
                    });
            },
            confirmImage(event) {
                this.confirm_message = "";
                this.file = event.target.files[0];
                if (!this.file.type.match('image.*') &&
                    !this.file.type.match('video.*')) {
                    this.confirm_message = '画像または動画ファイルを選択してください';
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
    table {
        /* width: 100%; */
        border-collapse: separate;
        border-spacing: 0;
    }

    table th:first-child {
        border-radius: 5px 0 0 0;
    }

    table th:last-child {
        border-radius: 0 5px 0 0;
        border-right: 1px solid #3c6690;
    }

    table th {
        text-align: center;
        color: white;
        background: linear-gradient(#829ebc, #225588);
        border-left: 1px solid #3c6690;
        border-top: 1px solid #3c6690;
        border-bottom: 1px solid #3c6690;
        box-shadow: 0px 1px 1px rgba(255, 255, 255, 0.3) inset;
        padding: 10px 0;
    }

    .th_id {
        width: 50px;
    }

    .th_title {
        width: 120px;
        resize: horizontal;
        overflow: auto;
    }

    .th_author {
        width: 120px;
        resize: horizontal;
        overflow: auto;
    }

    .th_commet {
        width: 300px;
        resize: horizontal;
        overflow: auto;
    }

    .th_image {
        width: 100px;
    }

    .th_edit {
        width: 50px;
    }
    .th_delete {
        width: 50px;
    }

    table td {
        text-align: center;
        border-left: 1px solid #a8b7c5;
        border-bottom: 1px solid #a8b7c5;
        border-top: none;
        box-shadow: 0px -3px 5px 1px #eee inset;
        padding: 10px 0;
        text-overflow: ellipsis;
        white-space: nowrap;
        overflow: hidden;
        max-width: 0;
    }

    table td:last-child {
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

    .modal-wrapper {
        z-index: 999;
        position: fixed;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
        padding: 40px 10px;
        text-align: center
    }

    .modal-wrapper:target {
        opacity: 1;
        visibility: visible;
        transition: opacity .4s, visibility .4s;
    }

    .modal-wrapper::after {
        display: inline-block;
        height: 100%;
        margin-left: -.05em;
        vertical-align: middle;
        content: ""
    }

    .modal-wrapper .modal-window {
        box-sizing: border-box;
        display: inline-block;
        z-index: 20;
        position: relative;
        width: 70%;
        max-width: 600px;
        padding: 30px 30px 15px;
        border-radius: 2px;
        background: #fff;
        box-shadow: 0 0 30px rgba(0, 0, 0, .6);
        vertical-align: middle
    }

    .modal-wrapper .modal-window .modal-content {
        max-height: 80vh;
        overflow-y: auto;
        text-align: left
    }

    .modal-overlay {
        z-index: 10;
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
        background: rgba(0, 0, 0, .8)
    }

    .modal-wrapper .modal-close {
        z-index: 20;
        position: absolute;
        top: 0;
        right: 0;
        width: 35px;
        color: #95979c !important;
        font-size: 20px;
        font-weight: 700;
        line-height: 35px;
        text-align: center;
        text-decoration: none;
        text-indent: 0
    }

    .modal-wrapper .modal-close:hover {
        color: #2b2e38 !important
    }

    #x-button {
        background-color: transparent;
        border: none;
        cursor: pointer;
        outline: none;
        padding: 0;
        appearance: none;
    }
</style>
