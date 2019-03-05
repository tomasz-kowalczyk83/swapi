<template>
    <div v-cloak>
        <h3>swapi</h3>
        <div v-if="showConfirmScreen">
            <h1 class="text-center">You Selected:</h1>
            <ul class="row">
                <div class="col-sm-4 py-3"
                     v-for="person in selected"
                     :key="person.id"
                     :class="{ selected: person.selected}">
                    <li class="card">

                        <div class="card-body d-flex justify-content-between">
                            <div>
                                <h5 class="card-title">{{ person.name }}</h5>
                                <p>Height: {{ person.height }}</p>
                                <p>Mass: {{ person.mass }}</p>
                            </div>
                            <div class="d-flex flex-column align-items-center">
                                <div class="toggle"></div>
                            </div>
                        </div>
                    </li>
                </div>
            </ul>
            <p class="text-center">
                <button @click="download" class="btn btn-primary btn-lg">Download Data</button>
            </p>
        </div>
        <div v-else>
            <div v-if="loading" class="loading-container">
                <spinner size="massive"></spinner>
            </div>
            <h1 class="text-center">Select Your Three Favourite Characters:</h1>
            <div class="paginator d-flex justify-content-center">
                <div v-if="paginator.prev">
                    <div @click="getPeople(paginator.prev)" class="prev">
                        <span class="oi oi-caret-left"></span>
                    </div>
                </div>
                <div class="label mx-2">page {{ paginator.page }} of {{ paginator.total }}</div>
                <div v-if="paginator.next">
                    <div @click="getPeople(paginator.next)" class="next">
                        <span class="oi oi-caret-right"></span>
                    </div>
                </div>
            </div>
            <ul class="row">
                <div class="col-sm-4 py-3"
                     @click="toggle(person)"
                     v-for="person in people"
                     :key="person.id"
                     :class="{ selected: person.selected}">
                    <li class="card">

                        <div class="card-body d-flex justify-content-between">
                            <div>
                                <h5 class="card-title">{{ person.name }}</h5>
                                <p>Height: {{ person.height }}</p>
                                <p>Mass: {{ person.mass }}</p>
                                <p>url: {{ person.url }}</p>
                            </div>
                            <div class="d-flex flex-column align-items-center">
                                <div class="toggle"></div>
                            </div>
                        </div>
                    </li>
                </div>
            </ul>
            <div class="text-center">
                <p>You selected: </p>
                <p>{{selectedLabel}}</p>
            </div>
            <p class="text-center">
                <button @click="showConfirmScreenUrl" :disabled="!confirmBtn" class="btn btn-primary btn-lg">Confirm</button>
            </p>
        </div>
    </div>
</template>

<script>
    import axios from 'axios'
    import Spinner from 'vue-simple-spinner'

    export default {
        components: {
            Spinner
        },

        data: function() {
            return {
                loading: true,
                people: [],
                selected: [],
                showConfirmScreen: false,
                paginator: {
                    page: 1,
                    prev: null,
                    next: null,
                    total: 0
                }
            }
        },

        watch: {
            people: {
                handler: function(after, before) {
                    console.log(after, before);
                    if(before.length) {
                        // Return the object that changed
                        let changed = after.filter( function( p, idx ) {
                            console.log(p.selected, before[idx].selected);
                            return p.selected !== before[idx].selected;
                            // return Object.keys(p).some( function( prop ) {
                            //     console.log(prop, p[prop], before[idx][prop])
                            //     return p[prop] !== before[idx][prop];
                            // })
                        })
                        // Log it
                        console.log(changed)

                    }
                    return after;
                },
                deep: true,
            }
        },

        computed: {
            selectedLabel: function() {
                return this.selected.map(p => p.name).join(',');
            },
            confirmBtn: function() {
                return this.selected.length >= 3;
            }
        },

        methods: {
            showConfirmScreenUrl() {
                window.location.href = '#/confirm';
            },

            toggle(person) {
                console.log(person);
                if(!person.selected && this.selected.length === 3) {
                    alert('You can only select three characters.');
                    return;
                }
                this.$set(person, 'selected', !person.selected);
                // Object.assign({}, obj)
                if(person.selected) {
                    this.selected.push(person)
                } else {
                    this.selected.splice(this.selected.indexOf(person))
                }

            },

            getPeople(page) {
                console.log('get people', page)
                this.paginator.page = page;

                axios
                    .get('/api/swapi/people', {
                        params: {
                            page: page
                        }
                    })
                    .then((response) => {
                        this.$set(this.paginator, 'total', response.data.total);
                        this.$set(this.paginator, 'prev', response.data.prev);
                        this.$set(this.paginator, 'next', response.data.next);

                        this.people = response.data.results;
                        this.people.forEach(function(person, index) {
                            person.id = index;
                            // this.$set(person, 'selected', false);
                        });
                    })
                    .catch((error) => {
                        console.log(error);
                    })
                    .finally(() => {
                        this.loading = false;
                    });
            },

            download() {
                axios
                    .post('api/swapi/people/download', this.selected, {responseType: 'arraybuffer'}
                    )
                    .then((response) => {
                        console.log(response);
                        let a = document.createElement("a");
                        document.body.appendChild(a);
                        a.style = "display: none";

                        let blob = new Blob([response.data], { type: 'text/csv' }),
                            url = window.URL.createObjectURL(blob);

                        a.href = url;
                        a.download = 'people.csv';
                        a.click();
                        window.URL.revokeObjectURL(url);
                    })
                    .catch((error) => {
                        console.log(error);
                    })
                    .finally(() => {
                        this.loading = false;
                    })
            }
        },

        created: function() {
            this.getPeople(1);
        },

        mounted() {
            let self = this;
            window.location.hash = '';
            window.addEventListener('hashchange', function() {
                let hash = window.location.hash.replace(/#\/?/, '')

                self.showConfirmScreen = hash === 'confirm';
            })

        }
    }


</script>

<style scoped>
    .loading-container {
        position: absolute;
        background: #fff;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
        display: flex;
        flex-direction: column;
        justify-content: center;
        z-index: 2;
    }
    .toggle {
        text-align: center;
        width: 40px;
        /* auto, since non-WebKit browsers doesn't support input styling */
        height: auto;
        margin: auto 0;
        border: none; /* Mobile Safari */
        -webkit-appearance: none;
        appearance: none;
    }

    .toggle:after {
        content: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="-10 -18 100 135"><circle cx="50" cy="50" r="50" fill="none" stroke="#ededed" stroke-width="3"/></svg>');
    }

    .selected .toggle:after {
        content: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="-10 -18 100 135"><circle cx="50" cy="50" r="50" fill="none" stroke="#bddad5" stroke-width="3"/><path fill="#5dc2af" d="M72 25L42 71 27 56l-4 4 20 20 34-52z"/></svg>');
    }
    .paginator .prev,
    .paginator .next {
        cursor: pointer;
    }
</style>