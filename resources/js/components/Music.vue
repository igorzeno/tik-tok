<template>
    <div class="page__center wide">
        <div class="page__row page__row_head" v-if="!mobile">
            <div class="page__col col__header d-flex flex-column flex-sm-row justify-content-space-between">
                <div class="mt-4 mt-sm-0">
                    <div class="page__hello h5" v-if="user" v-html="user.name+','"/>
                    <div class="page__welcome h2">Привет 👋</div>
                </div>

                <div class="mt-4 mt-sm-0" v-if="items && items.length">
                    <p class="mb-2">
                        <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle class="color" cx="7" cy="7" r="6.5" fill="#7FBA7A"/>
                        </svg>

                        <span class="color-gray ml-2 align-middle">На продвижении</span>
                    </p>
                    <p>
                        <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle class="color" cx="7" cy="7" r="6.5" fill="#FF4242"/>
                        </svg>

                        <span class="color-gray ml-2 align-middle">Есть заявки</span>
                    </p>
                </div>
            </div>
        </div>

        <div class="page__row" v-if="mobile">
            <div class="d-flex flex-row align-items-center justify-content-between">
                <h1>Мои треки</h1>
                <img :src="url + 'img/icon_plus_main.svg'" @click="openMusicModal" class="mb-2" />
            </div>
        </div>

        <div class="page__row" v-if="mobile">
             <div class="page__panel">
                <p>
                    <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle class="color" cx="6" cy="6" r="5.5" fill="#7FBA7A"/>
                    </svg>

                    <span>На продвижении</span>
                </p>
                <p>
                    <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle class="color" cx="6" cy="6" r="5.5" fill="#FF4242"/>
                    </svg>

                    <span>Есть заявки</span>
                </p>
            </div>
        </div>

        <div class="page__row page__row_border">
            <div class="page__col">
                <div class="products__grid" v-if="items && items.length">
                    <div class="products__item" @click="openMusicModal" v-if="!mobile">
                        <div class="products__preview new"></div>
                        <div class="products__details">
                            <div class="products__title track">Добавить трек</div>
                        </div>
                    </div>
                    <div class="products__item" v-for="item in items">
                        <div class="products__preview">
                            <img :src="item.image">
                        </div>
                        <div class="products__details author">
                            <div class="products__title author">{{ item.author }}</div>
                            <div class="products__title title">{{ item.title }}</div>
                        </div>
                    </div>
                </div>

                <div class="banner__grid" v-else>
                    <div class="banner__item" @click="openMusicModal">
                        <div class="banner__details">
                            <div class="banner__title"><h3>Загрузи свой первый трек</h3></div>
                            <div class="banner__text">Твои будущие фанаты ждут!
                                Жми на кнопку <q>добавить трек</q> и переходи к продвижению прямо сейчас.</div>
                            <div class="banner__button">Добавить трек</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <b-modal id="music-modal" centered hide-footer>
            <div class="modal-center d-flex flex-column text-center mx-auto">
                <div class="form-block">
                    <input type="text" class="form-control" placeholder="Ссылка на звук в TikTok" v-model="val" required="" />
                    <p class="form-tip text-danger" v-if="error" v-html="error" />
                    <button class="btn btn-lg btn-primary btn-block my-4" @click="getMusic(val)" :disabled="!val" v-if="!waiting" v-html="val ? 'Найти трек' : 'Введите ссылку на трек'" />
                    <div class="loading" :class="{active: waiting}" />
                    <p class="form-tip" v-if="waiting" v-html="'Ищем трек, это займет от 5 до 10 секунд'" />
                </div>
            </div>
        </b-modal>

        <b-modal id="music-store" centered hide-footer>
            <div class="modal-center d-flex flex-column text-center mx-auto">
                <div class="form-block labels">
                    <div class="form-block img"><img :src="this.img"></div>
                    <label for="url">Ссылка на звук в TikTok</label>
                    <input type="text" id="url" class="form-control" v-model="val" required="" />
                    <label for="title">Название</label>
                    <input type="text" id="title" class="form-control" v-model="title" required="" />
                    <label for=author>Исполнитель</label>
                    <input type="text" id="author" class="form-control" v-model="author" required="" />
                    <label for="album">Альбом</label>
                    <input type="text" id="album" class="form-control" v-model="album" required="" />
                    <p class="form-tip text-danger" v-if="error" v-html="error" />
                    <button class="btn btn-lg btn-primary btn-block my-4" @click="addMusic()" :disabled="!val || !title" v-html="val ? (title ? 'Добавить' :'Укажите название') : 'Введите ссылку на трек'" />
                </div>
            </div>
        </b-modal>
    </div>
</template>

<script>
    import axios from "axios"
    import {mapGetters} from "vuex";
    import { GET_MUSIC_LIST, GET_MUSIC, ADD_MUSIC } from '../api-routes';

    export default {
        components: { },
        name: "Music",

        data() {
            return {
                editing: false,
                val: this.value,
                music: null,
                error: null,
                waiting: false,
                items: null,
                img: null,
                title: null,
                author: null,
                album: null,
            }
        },

        mounted() {
            this.getMusicList();
        },

        computed: {
            ...mapGetters(['user', 'userAuthorized', 'url', 'tablet', 'mobile']),
        },

        methods: {
            openMusicModal() {
                this.waiting = this.error = false;
                this.$bvModal.show('music-modal');
            },

            openMusicStore() {
                this.error = false;
                this.$bvModal.show('music-store');
            },

            getMusicList() {
                axios.get(GET_MUSIC_LIST).then(response => {
                    this.items = response.data;
                   // console.log(this.items.length);
                });
            },

            addMusic(){
                axios.post( ADD_MUSIC,{
                    url: this.val,
                    title: this.title,
                    author: this.author,
                    album: this.album,
                }).then(response => {
                    this.waiting = false;
                    this.$bvModal.hide('music-store');
                    this.getMusicList();
                }).catch(error => {
                    console.log(error);
                });
            },

            getMusic(url) {
                this.waiting = true;
                this.error = null;

                axios.post(GET_MUSIC, {url: url}).then(response => {
                    this.waiting = false;

                    /* TO DO */
                    //console.log(response.data.music.coverMedium);
                    if(response.data.music != undefined){
                        this.title = response.data.music.title;
                        this.author = response.data.music.authorName;
                        this.album = response.data.music.album;
                        this.img = response.data.music.coverMedium;
                        this.$bvModal.hide('music-modal');
                        this.openMusicStore();
                    }

                    this.error = null;
                }).catch(error => {
                    console.log(error);
                    this.waiting = false;
                    if(error !== undefined) {
                        this.error = 'Не удалось найти трек, попробуйте еще раз';
                    }
                });
            }
        }

    }
</script>
