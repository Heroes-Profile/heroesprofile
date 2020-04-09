<template>
    <div class="heroes">

        <div class="">

            <b-input-group size="sm" prepend="Search">
                <b-form-input v-model="search"></b-form-input>
                <b-input-group-append>
                    <b-button @click="search = ''" variant="secondary">
                        &times
                    </b-button>
                     <b-button @click="selection = []">Reset</b-button>
                </b-input-group-append>
            </b-input-group>
        
    
        </div>
        <div class="roles">
            <b-button v-for="role in roles" :key="role" class="role" variant="link" @click="selectRole(role)">
                <img class="role-img" :alt="role" :src="'/images/roles/'+role.toLowerCase()+'.PNG'" />
            </b-button>
        </div>
    
        Selection:
        <b-form-checkbox-group buttons v-model="selection" class="hero-images-checkbox">
            <b-form-checkbox v-for="hero in fullHeroes(selection)" :value="hero.name" :key="hero.name">
                <image-popup :alttext="hero.name" :imgSrc="'/images/heroes/'+hero.short_name+'.png'"></image-popup>
                {{hero.name}}
            </b-form-checkbox>
        </b-form-checkbox-group>
    
        Unselected:
        <b-form-checkbox-group buttons v-model="selection" class="hero-images-checkbox">
            <b-form-checkbox v-for="hero in fullHeroes(unselectedHeroes)" :value="hero.name" :key="hero.name">
                <image-popup :alttext="hero.name" :imgSrc="'/images/heroes/'+hero.short_name+'.png'"></image-popup>
                {{hero.name}}
            </b-form-checkbox>
        </b-form-checkbox-group>

    </div>
</template>


<style lang="scss" scoped>
.roles {
    display: flex;
}

.role {
    padding: 10px;
}

.role-img {
    // width: 40px;
    height: 40px;
}

.heroes {
    height: 500px;
    overflow: scroll;
}

.filter-list {
    display: grid;
    padding: 1rem;
    padding-left: 2rem;
    .item {
        padding: 5px 0;
    }
}
</style>

<script>
import { mapState } from "vuex";
import { mapGetters } from "vuex";
import ImagePopup from "@/components/ImagePopup.vue";

export default {
    data: function () {
        return {
            search: '',
        }
    },
    methods: {
        selectRole: function(role) {
            let role_heroes = _.filter(this.heroes, (h) => {
                return h.new_role === role
            })
            this.selection = _.union(this.selection, _.map(role_heroes, 'name'))
        },
        fullHeroes: function(names) {
            return _.map(names, (n) => {
                return _.find(this.heroes, ['name', n]);
            })
        }
    },
    components: {
        ImagePopup,
    },
    computed: {
        ...mapGetters({
            heroes: 'fieldStore/heroes',
            selectedGameTypes: 'searchStore/selectedGameTypes'

        }),
        ...mapState({
            selectedHeroes: state => state.searchStore.selectedHeroes,
        }),
        roles() {
            return _.chain(this.heroes).map('new_role').uniq().value()
        },
        showHeroes() { 
            if (this.search.length == 0) {
                return this.heroes
            }
            return _.filter(this.heroes, (h) => {
                return h.name.toLowerCase().includes(this.search.toLowerCase())
            })
        },
        heroNames() {
            return _.map(this.heroes, 'name')
        },
        unselectedHeroes() {
            return _.filter(this.heroNames, (h) => {
                let search = true
                if (this.search.length != 0) {
                   search =  h.toLowerCase().includes(this.search.toLowerCase())
                }           
               
                return this.selection.indexOf(h) == -1 && search
            })
        },
        selection: {
            get: function() {
                return this.selectedHeroes || []
            },
            set: function(values) {
                this.$store.dispatch('searchStore/PUSH_HEROES', values)
            }
        },

    }

}
</script>