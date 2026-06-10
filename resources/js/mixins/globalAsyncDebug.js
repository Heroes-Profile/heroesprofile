export default {
  data() {
    return {
      loadMeta: null,
      loadDebugStatus: null,
    };
  },
  methods: {
    prepareGlobalAsyncLoad(cancelToken = null) {
      this.loadMeta = this.$createLoadMeta();
      this.loadDebugStatus = null;

      const options = {
        loadMeta: this.loadMeta,
        onLoadStatus: (message) => {
          this.loadDebugStatus = message;
        },
      };

      if (cancelToken) {
        options.cancelToken = cancelToken;
      }

      return options;
    },
  },
};
