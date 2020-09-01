<template>
    <div class="flex flex-col items-center" v-if="status.user === 'success' && user">
        <div class="relative mb-8">
            <div class="w-100 h-64 overflow-hidden z-10">
                <img
                    src="https://www.nationalgeographic.com/content/dam/photography/photos/000/000/6.ngsversion.1467942028599.adapt.1900.1.jpg"
                    alt="user background image"
                    class="object-cover w-full"
                >
            </div>

            <div class="absolute flex items-center bottom-0 left-0 -mb-8 ml-12 z-20">
                <div class="w-32">
                    <img src="https://visualpharm.com/assets/387/Person-595b40b75ba036ed117da139.svg"
                         alt="profile picture"
                         class="object-cover w-32 h-32 border-4 border-gray-200 rounded-full shadow-lg"
                    >
                </div>

                <p class="ml-4 text-2xl text-gray-100">{{ user.data.attributes.name }}</p>
            </div>

            <div class="absolute flex items-center bottom-0 right-0 mb-4 mr-12 z-20">
                <button
                    v-if="friendButtonText && friendButtonText !== 'Accept'"
                    class="py-1 px-3 bg-gray-400 rounded"
                    @click="$store.dispatch('sendFriendRequest', $route.params.userId)"
                >
                    {{ friendButtonText }}
                </button>
                <button
                    v-if="friendButtonText && friendButtonText === 'Accept'"
                    class="mr-2 py-1 px-3 bg-blue-500 rounded"
                    @click="$store.dispatch('acceptFriendRequest', $route.params.userId)"
                >
                    Accept
                </button>
                <button
                    v-if="friendButtonText && friendButtonText === 'Accept'"
                    class="py-1 px-3 bg-gray-400 rounded"
                    @click="$store.dispatch('ignoreFriendRequest', $route.params.userId)"
                >
                    Ignore
                </button>
            </div>
        </div>

        <div v-if="status.posts === 'loading'">Loading posts...</div>

        <div v-else-if="posts.length < 1">No posts found.</div>

        <Post
            v-else
            v-for="post in posts.data"
            :key="post.data.post_id"
            :post="post"
        ></Post>
    </div>
</template>

<script>
    import Post from '../../components/Post';
    import {mapGetters} from 'vuex';

    export default {
        name: "Show",

        components: {
            Post,
        },

        mounted() {
            this.$store.dispatch('fetchUser', this.$route.params.userId);
            this.$store.dispatch('fetchUserPosts', this.$route.params.userId);
        },

        computed: {
            ...mapGetters({
                user: 'user',
                posts: 'posts',
                status: 'status',
                friendButtonText: 'friendButtonText',
            }),
        }
    }
</script>

<style scoped>

</style>
